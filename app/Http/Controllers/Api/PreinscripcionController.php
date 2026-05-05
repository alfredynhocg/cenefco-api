<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PreinscripcionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $baseRules = [
            'programa_id'       => ['nullable', 'integer'],
            'nombre'            => ['required', 'string', 'max:200'],
            'apellido_paterno'  => ['nullable', 'string', 'max:100'],
            'apellido_materno'  => ['nullable', 'string', 'max:100'],
            'ci'                => ['nullable', 'string', 'max:30'],
            'expedido_id'       => ['nullable', 'integer'],
            'fecha_nacimiento'  => ['nullable', 'date'],
            'email'             => ['required', 'email', 'max:100'],
            'telefono'          => ['nullable', 'string', 'max:20'],
            'ciudad'            => ['nullable', 'string', 'max:120'],
            'provincia'         => ['nullable', 'string', 'max:120'],
            'medio_pago'        => ['nullable', 'string', 'max:100'],
            'monto_pagado'      => ['nullable', 'numeric'],
            'archivo_ci_anverso'=> ['nullable', 'string', 'max:255'],
            'archivo_titulo'    => ['nullable', 'string', 'max:255'],
            'archivo_cv'        => ['nullable', 'string', 'max:255'],
            'archivo_foto_3x3'  => ['nullable', 'string', 'max:255'],
            'sugerencia_curso'  => ['nullable', 'string'],
            'recomendar_docente'=> ['nullable', 'boolean'],
            'detalle_docente'   => ['nullable', 'string'],
            'mensaje'           => ['nullable', 'string'],
            'origen'            => ['nullable', 'string', 'max:100'],
            'campos_extra'      => ['nullable', 'array'],
        ];

        $camposDefinicion = [];
        $programaId = $request->input('programa_id');
        if ($programaId) {
            $programa = DB::table('t_programa')->where('id_programa', $programaId)->first();
            if ($programa && $programa->categoria_web_id) {
                $camposDefinicion = DB::table('web_categoria_campo')
                    ->where('categoria_id', $programa->categoria_web_id)
                    ->where('activo', true)
                    ->get();

                foreach ($camposDefinicion as $campo) {
                    $regla = ['nullable'];

                    $regla[] = match ($campo->tipo_campo) {
                        'email'  => 'email',
                        'number' => 'numeric',
                        'date'   => 'date',
                        'boolean'=> 'boolean',
                        default  => 'string',
                    };

                    $baseRules["campos_extra.{$campo->nombre_campo}"] = $regla;
                }
            }
        }

        $data = $request->validate($baseRules);

        if (isset($data['campos_extra']) && is_array($data['campos_extra'])) {
            $data['campos_extra'] = json_encode($data['campos_extra']);
        }

        $data['estado']     = 'pendiente';
        $data['ip_origen']  = $request->ip();
        $data['created_at'] = now()->toDateTimeString();
        $data['updated_at'] = now()->toDateTimeString();

        $id = DB::table('web_preinscripcion')->insertGetId($data);

        $row = DB::table('web_preinscripcion')->where('id', $id)->first();
        if ($row && is_string($row->campos_extra)) {
            $row->campos_extra = json_decode($row->campos_extra);
        }

        return response()->json($row, 201);
    }

    public function index(Request $request): JsonResponse
    {
        $q = DB::table('web_preinscripcion as p')
            ->leftJoin('web_expedido as exp', 'p.expedido_id', '=', 'exp.id')
            ->leftJoin('web_grado_academico as ga', 'p.grado_academico_id', '=', 'ga.id')
            ->select(
                'p.*',
                'exp.nombre as expedido_nombre',
                'ga.nombre as grado_academico_nombre',
                'ga.abreviatura as grado_abreviatura',
            );

        if ($search = $request->get('query')) {
            $q->where(function ($sub) use ($search) {
                $sub->where('p.nombre', 'like', "%{$search}%")
                    ->orWhere('p.email', 'like', "%{$search}%")
                    ->orWhere('p.ci', 'like', "%{$search}%")
                    ->orWhere('p.telefono', 'like', "%{$search}%");
            });
        }

        if ($estado = $request->get('estado')) {
            $q->where('p.estado', $estado);
        }

        if ($programa = $request->get('programa_id')) {
            $q->where('p.programa_id', $programa);
        }

        $pageSize = (int) $request->get('pageSize', 15);
        $pageIndex = (int) $request->get('pageIndex', 1);
        $offset = ($pageIndex - 1) * $pageSize;

        $total = $q->count();
        $items = (clone $q)
            ->orderBy('p.created_at', 'desc')
            ->offset($offset)
            ->limit($pageSize)
            ->get()
            ->map(function ($row) {
                if (is_string($row->campos_extra)) {
                    $row->campos_extra = json_decode($row->campos_extra);
                }

                return $row;
            });

        return response()->json(['data' => $items, 'total' => $total]);
    }

    public function show(int $id): JsonResponse
    {
        $item = DB::table('web_preinscripcion as p')
            ->leftJoin('web_expedido as exp', 'p.expedido_id', '=', 'exp.id')
            ->leftJoin('web_grado_academico as ga', 'p.grado_academico_id', '=', 'ga.id')
            ->select(
                'p.*',
                'exp.nombre as expedido_nombre',
                'ga.nombre as grado_academico_nombre',
                'ga.abreviatura as grado_abreviatura',
            )
            ->where('p.id', $id)
            ->first();

        if (! $item) {
            abort(404, 'Preinscripción no encontrada');
        }

        if (is_string($item->campos_extra)) {
            $item->campos_extra = json_decode($item->campos_extra);
        }

        return response()->json($item);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        if (! DB::table('web_preinscripcion')->where('id', $id)->exists()) {
            abort(404, 'Preinscripción no encontrada');
        }

        $data = $request->validate([
            'estado' => ['required', 'in:pendiente,revisado,aceptado,rechazado,contactado'],
            'notificado' => ['nullable', 'boolean'],
        ]);

        $data['updated_at'] = now()->toDateTimeString();
        if (isset($data['notificado']) && $data['notificado']) {
            $data['fecha_notificacion'] = now()->toDateTimeString();
        }

        DB::table('web_preinscripcion')->where('id', $id)->update($data);

        return response()->json(DB::table('web_preinscripcion')->where('id', $id)->first());
    }

    public function destroy(int $id): JsonResponse
    {
        if (! DB::table('web_preinscripcion')->where('id', $id)->delete()) {
            abort(404);
        }

        return response()->json(null, 204);
    }
}
