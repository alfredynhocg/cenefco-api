<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MdlCourseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $size = (int) $request->get('pageSize', 50);
        $page = (int) $request->get('pageIndex', 1);

        $q = DB::table('mdl_course');
        if ($request->has('id_us_reg')) {
            $q->where('id_us_reg', (int) $request->get('id_us_reg'));
        }
        if ($request->has('id_docente')) {
            $q->where('id_docente', (int) $request->get('id_docente'));
        }
        if (! $request->boolean('conInactivos', false)) {
            $q->where('estado', 1);
        }

        $total = $q->count();
        $data = $q->orderByDesc('fecha_reg')->offset(($page - 1) * $size)->limit($size)->get();

        return response()->json(['data' => $data, 'total' => $total]);
    }

    public function show(int $id): JsonResponse
    {
        $row = DB::table('mdl_course')->where('id', $id)->first();
        if (! $row) {
            abort(404, 'Curso Moodle no encontrado');
        }

        return response()->json($row);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id' => ['required', 'integer'],
            'id_us_reg' => ['nullable', 'integer'],
            'num_modcurso' => ['nullable', 'integer'],
            'fullname' => ['nullable', 'string', 'max:254'],
            'shortname' => ['nullable', 'string', 'max:255'],
            'id_docente' => ['nullable', 'integer'],
            'category' => ['nullable', 'integer'],
            'sigla' => ['nullable', 'string', 'max:200'],
            'paralelo' => ['nullable', 'string', 'max:200'],
            'cupo' => ['nullable', 'string', 'max:200'],
            'grado_academico1' => ['nullable', 'string', 'max:200'],
            'grado_academico2' => ['nullable', 'string', 'max:200'],
            'observacion_imp' => ['nullable', 'string'],
            'imparte_fecha_inicio' => ['nullable', 'date'],
            'imparte_fecha_fin' => ['nullable', 'date'],
            'imparte_fecha_acta' => ['nullable', 'date'],
            'ocultar_coordinador_acta' => ['nullable', 'integer'],
            'id_coordinador' => ['nullable', 'integer'],
            'grado_coordinador1' => ['nullable', 'string', 'max:200'],
            'grado_coordinador2' => ['nullable', 'string', 'max:200'],
            'titulo_personalizado' => ['nullable', 'string', 'max:200'],
            'subtitulo_personalizado' => ['nullable', 'string', 'max:200'],
            'gestion' => ['nullable', 'string', 'max:200'],
            'estado' => ['nullable', 'integer'],
            'per_modificar' => ['nullable', 'integer'],
        ]);
        $data['id_us_reg'] = $request->integer('id_us_reg', 0);
        $data['estado'] = $request->integer('estado', 1);
        $data['fecha_reg'] = now();

        DB::table('mdl_course')->insert($data);

        return response()->json(DB::table('mdl_course')->where('id', $data['id'])->first(), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = DB::table('mdl_course')->where('id', $id)->first();
        if (! $row) {
            abort(404, 'Curso Moodle no encontrado');
        }

        $data = $request->validate([
            'fullname' => ['nullable', 'string', 'max:254'],
            'shortname' => ['nullable', 'string', 'max:255'],
            'id_docente' => ['nullable', 'integer'],
            'category' => ['nullable', 'integer'],
            'sigla' => ['nullable', 'string', 'max:200'],
            'paralelo' => ['nullable', 'string', 'max:200'],
            'cupo' => ['nullable', 'string', 'max:200'],
            'grado_academico1' => ['nullable', 'string', 'max:200'],
            'grado_academico2' => ['nullable', 'string', 'max:200'],
            'observacion_imp' => ['nullable', 'string'],
            'imparte_fecha_inicio' => ['nullable', 'date'],
            'imparte_fecha_fin' => ['nullable', 'date'],
            'imparte_fecha_acta' => ['nullable', 'date'],
            'ocultar_coordinador_acta' => ['nullable', 'integer'],
            'id_coordinador' => ['nullable', 'integer'],
            'grado_coordinador1' => ['nullable', 'string', 'max:200'],
            'grado_coordinador2' => ['nullable', 'string', 'max:200'],
            'titulo_personalizado' => ['nullable', 'string', 'max:200'],
            'subtitulo_personalizado' => ['nullable', 'string', 'max:200'],
            'gestion' => ['nullable', 'string', 'max:200'],
            'estado' => ['nullable', 'integer'],
            'per_modificar' => ['nullable', 'integer'],
        ]);
        DB::table('mdl_course')->where('id', $id)->update($data);

        return response()->json(DB::table('mdl_course')->where('id', $id)->first());
    }

    public function destroy(int $id): JsonResponse
    {
        DB::table('mdl_course')->where('id', $id)->update(['estado' => 0]);

        return response()->json(null, 204);
    }
}
