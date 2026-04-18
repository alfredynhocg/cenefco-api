<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ModuloAcademicoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = $request->get('query', '');
        $size = (int) $request->get('pageSize', 50);
        $page = (int) $request->get('pageIndex', 1);

        $q = DB::table('t_modulo');
        if ($query) {
            $q->where('titulo', 'like', "%{$query}%");
        }
        if ($request->has('posicion')) {
            $q->where('posicion', $request->get('posicion'));
        }
        if (! $request->boolean('conInactivos', false)) {
            $q->where('estado', 1);
        }

        $total = $q->count();
        $data = $q->orderBy('titulo')->offset(($page - 1) * $size)->limit($size)->get();

        return response()->json(['data' => $data, 'total' => $total]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $row = DB::table('t_modulo')->where('id_mod', $id)->first();
        if (! $row) {
            abort(404, 'Módulo no encontrado');
        }

        return response()->json($row);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_mod' => ['required', 'numeric'],
            'id_us_reg' => ['nullable', 'integer'],
            'num_mod' => ['nullable', 'integer'],
            'titulo' => ['required', 'string', 'max:200'],
            'mostrar_titulo' => ['nullable', 'integer'],
            'posicion' => ['nullable', 'string', 'max:200'],
            'direccion' => ['nullable', 'string', 'max:200'],
            'tipo' => ['nullable', 'string', 'max:200'],
            'id_niv' => ['nullable', 'string', 'max:11'],
            'usar_nivel_global' => ['nullable', 'integer'],
            'acceso' => ['nullable', 'string', 'max:200'],
            'asignacion' => ['nullable', 'integer'],
            'menu_asignado' => ['nullable', 'string'],
            'clase_php' => ['nullable', 'string', 'max:200'],
            'id_niv_ex' => ['nullable', 'string'],
            'estado' => ['nullable', 'integer'],
        ]);
        $data['id_us_reg'] = $request->integer('id_us_reg', 0);
        $data['num_mod'] = $request->integer('num_mod', 0);
        $data['estado'] = $request->integer('estado', 1);
        $data['fecha_reg'] = now();

        DB::table('t_modulo')->insert($data);

        return response()->json(DB::table('t_modulo')->where('id_mod', $data['id_mod'])->first(), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = DB::table('t_modulo')->where('id_mod', $id)->first();
        if (! $row) {
            abort(404, 'Módulo no encontrado');
        }

        $data = $request->validate([
            'titulo' => ['sometimes', 'required', 'string', 'max:200'],
            'mostrar_titulo' => ['nullable', 'integer'],
            'posicion' => ['nullable', 'string', 'max:200'],
            'direccion' => ['nullable', 'string', 'max:200'],
            'tipo' => ['nullable', 'string', 'max:200'],
            'id_niv' => ['nullable', 'string', 'max:11'],
            'usar_nivel_global' => ['nullable', 'integer'],
            'acceso' => ['nullable', 'string', 'max:200'],
            'asignacion' => ['nullable', 'integer'],
            'menu_asignado' => ['nullable', 'string'],
            'clase_php' => ['nullable', 'string', 'max:200'],
            'id_niv_ex' => ['nullable', 'string'],
            'estado' => ['nullable', 'integer'],
        ]);
        DB::table('t_modulo')->where('id_mod', $id)->update($data);

        return response()->json(DB::table('t_modulo')->where('id_mod', $id)->first());
    }

    public function destroy(int $id): JsonResponse
    {
        DB::table('t_modulo')->where('id_mod', $id)->update(['estado' => 0]);

        return response()->json(null, 204);
    }
}
