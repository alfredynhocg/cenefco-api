<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProgramaAcademicoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = $request->get('query', '');
        $size = (int) $request->get('pageSize', 30);
        $page = (int) $request->get('pageIndex', 1);

        $q = DB::table('t_programa');
        if ($query) {
            $q->where('nombre_programa', 'like', "%{$query}%");
        }
        if ($request->has('id_tipoprograma')) {
            $q->where('id_tipoprograma', (int) $request->get('id_tipoprograma'));
        }
        if (! $request->boolean('conInactivos', false)) {
            $q->where('estado', 1);
        }

        $total = $q->count();
        $data = $q->orderBy('nombre_programa')->offset(($page - 1) * $size)->limit($size)->get();

        return response()->json(['data' => $data, 'total' => $total]);
    }

    public function show(int $id): JsonResponse
    {
        $row = DB::table('t_programa')->where('id_programa', $id)->first();
        if (! $row) {
            abort(404, 'Programa no encontrado');
        }

        return response()->json($row);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_programa' => ['required', 'integer'],
            'id_us_reg' => ['nullable', 'integer'],
            'num_programa' => ['nullable', 'integer'],
            'nombre_programa' => ['required', 'string', 'max:200'],
            'descripcion' => ['nullable', 'string'],
            'foto' => ['nullable', 'string', 'max:200'],
            'inicio_actividades' => ['nullable', 'date'],
            'finalizacion_actividades' => ['nullable', 'date'],
            'inicio_inscripciones' => ['nullable', 'date'],
            'titulo_documento1' => ['nullable', 'string', 'max:200'],
            'documento1' => ['nullable', 'string', 'max:200'],
            'titulo_documento2' => ['nullable', 'string', 'max:200'],
            'documento2' => ['nullable', 'string', 'max:200'],
            'titulo_documento3' => ['nullable', 'string', 'max:200'],
            'documento3' => ['nullable', 'string', 'max:200'],
            'titulo_documento4' => ['nullable', 'string', 'max:200'],
            'documento4' => ['nullable', 'string', 'max:200'],
            'dirigido' => ['nullable', 'string'],
            'inversion' => ['nullable', 'string'],
            'requisitos' => ['nullable', 'string'],
            'creditaje' => ['nullable', 'string'],
            'objetivo' => ['nullable', 'string'],
            'nota' => ['nullable', 'string'],
            'id_tipoprograma' => ['nullable', 'integer'],
            'url_video' => ['nullable', 'string', 'max:200'],
            'estado' => ['nullable', 'integer'],
        ]);
        $data['id_us_reg'] = $request->integer('id_us_reg', 0);
        $data['num_programa'] = $request->integer('num_programa', 0);
        $data['estado'] = $request->integer('estado', 1);
        $data['fecha_reg'] = now();

        DB::table('t_programa')->insert($data);

        return response()->json(DB::table('t_programa')->where('id_programa', $data['id_programa'])->first(), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = DB::table('t_programa')->where('id_programa', $id)->first();
        if (! $row) {
            abort(404, 'Programa no encontrado');
        }

        $data = $request->validate([
            'nombre_programa' => ['sometimes', 'required', 'string', 'max:200'],
            'descripcion' => ['nullable', 'string'],
            'foto' => ['nullable', 'string', 'max:200'],
            'inicio_actividades' => ['nullable', 'date'],
            'finalizacion_actividades' => ['nullable', 'date'],
            'inicio_inscripciones' => ['nullable', 'date'],
            'titulo_documento1' => ['nullable', 'string', 'max:200'],
            'documento1' => ['nullable', 'string', 'max:200'],
            'titulo_documento2' => ['nullable', 'string', 'max:200'],
            'documento2' => ['nullable', 'string', 'max:200'],
            'titulo_documento3' => ['nullable', 'string', 'max:200'],
            'documento3' => ['nullable', 'string', 'max:200'],
            'titulo_documento4' => ['nullable', 'string', 'max:200'],
            'documento4' => ['nullable', 'string', 'max:200'],
            'dirigido' => ['nullable', 'string'],
            'inversion' => ['nullable', 'string'],
            'requisitos' => ['nullable', 'string'],
            'creditaje' => ['nullable', 'string'],
            'objetivo' => ['nullable', 'string'],
            'nota' => ['nullable', 'string'],
            'id_tipoprograma' => ['nullable', 'integer'],
            'url_video' => ['nullable', 'string', 'max:200'],
            'estado' => ['nullable', 'integer'],
        ]);
        DB::table('t_programa')->where('id_programa', $id)->update($data);

        return response()->json(DB::table('t_programa')->where('id_programa', $id)->first());
    }

    public function destroy(int $id): JsonResponse
    {
        DB::table('t_programa')->where('id_programa', $id)->update(['estado' => 0]);

        return response()->json(null, 204);
    }
}
