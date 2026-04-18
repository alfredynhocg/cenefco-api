<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ListaAprobadosController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $size = (int) $request->get('pageSize', 30);
        $page = (int) $request->get('pageIndex', 1);

        $q = DB::table('t_lista_aprobados');
        if ($request->has('imparte_id')) {
            $q->where('imparte_id', (int) $request->get('imparte_id'));
        }
        if ($request->has('usuario_id')) {
            $q->where('usuario_id', (int) $request->get('usuario_id'));
        }
        if ($request->has('condicion')) {
            $q->where('condicion', $request->get('condicion'));
        }
        if ($request->has('estado_certificado')) {
            $q->where('estado_certificado', $request->get('estado_certificado'));
        }

        $total = $q->count();
        $data = $q->orderByDesc('id')->offset(($page - 1) * $size)->limit($size)->get();

        return response()->json(['data' => $data, 'total' => $total]);
    }

    public function show(int $id): JsonResponse
    {
        $row = DB::table('t_lista_aprobados')->find($id);
        if (! $row) {
            abort(404, 'Registro no encontrado');
        }

        return response()->json($row);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'imparte_id' => ['required', 'integer'],
            'usuario_id' => ['required', 'integer'],
            'inscripcion_id' => ['nullable', 'integer'],
            'nota_final' => ['nullable', 'numeric'],
            'nota_minima' => ['nullable', 'numeric'],
            'condicion' => ['nullable', 'string', 'max:50'],
            'observacion' => ['nullable', 'string', 'max:500'],
            'ajuste_manual' => ['nullable', 'boolean'],
            'estado_certificado' => ['nullable', 'string', 'max:50'],
            'registrado_por' => ['nullable', 'integer'],
            'id_us_reg' => ['nullable', 'integer'],
        ]);
        $data['condicion'] = $data['condicion'] ?? 'aprobado';
        $data['estado_certificado'] = $data['estado_certificado'] ?? 'pendiente';
        $data['ajuste_manual'] = $request->boolean('ajuste_manual', false);
        $data['notificado_email'] = false;
        $data['id_us_reg'] = $request->integer('id_us_reg', 0);
        $data['created_at'] = now();

        $id = DB::table('t_lista_aprobados')->insertGetId($data);
        $row = DB::table('t_lista_aprobados')->find($id);

        return response()->json($row, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = DB::table('t_lista_aprobados')->find($id);
        if (! $row) {
            abort(404, 'Registro no encontrado');
        }

        $data = $request->validate([
            'nota_final' => ['nullable', 'numeric'],
            'nota_minima' => ['nullable', 'numeric'],
            'condicion' => ['nullable', 'string', 'max:50'],
            'observacion' => ['nullable', 'string', 'max:500'],
            'ajuste_manual' => ['nullable', 'boolean'],
            'estado_certificado' => ['nullable', 'string', 'max:50'],
            'notificado_email' => ['nullable', 'boolean'],
        ]);
        $data['updated_at'] = now();
        DB::table('t_lista_aprobados')->where('id', $id)->update($data);

        return response()->json(DB::table('t_lista_aprobados')->find($id));
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = DB::table('t_lista_aprobados')->where('id', $id)->delete();
        if (! $deleted) {
            abort(404, 'Registro no encontrado');
        }

        return response()->json(null, 204);
    }
}
