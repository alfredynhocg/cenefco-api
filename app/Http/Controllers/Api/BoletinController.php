<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BoletinController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = $request->get('query', '');
        $size = (int) $request->get('pageSize', 30);
        $page = (int) $request->get('pageIndex', 1);

        $q = DB::table('t_boletin');
        if ($query) {
            $q->where('titulo_boletin', 'like', "%{$query}%");
        }
        if (! $request->boolean('conInactivos', false)) {
            $q->where('estado', 1);
        }

        $total = $q->count();
        $data = $q->orderByDesc('id_boletin')->offset(($page - 1) * $size)->limit($size)->get();

        return response()->json(['data' => $data, 'total' => $total]);
    }

    public function show(int $id): JsonResponse
    {
        $row = DB::table('t_boletin')->where('id_boletin', $id)->first();
        if (! $row) {
            abort(404, 'Boletín no encontrado');
        }

        return response()->json($row);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_boletin' => ['required', 'integer'],
            'id_us_reg' => ['nullable', 'integer'],
            'num_boletin' => ['nullable', 'integer'],
            'titulo_pagina' => ['nullable', 'string', 'max:200'],
            'titulo_boletin' => ['required', 'string', 'max:200'],
            'descripcion_boletin' => ['nullable', 'string'],
            'estado' => ['nullable', 'integer'],
        ]);
        $data['id_us_reg'] = $request->integer('id_us_reg', 0);
        $data['num_boletin'] = $request->integer('num_boletin', 0);
        $data['estado'] = $request->integer('estado', 1);
        $data['fecha_reg'] = now();

        DB::table('t_boletin')->insert($data);

        return response()->json(DB::table('t_boletin')->where('id_boletin', $data['id_boletin'])->first(), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = DB::table('t_boletin')->where('id_boletin', $id)->first();
        if (! $row) {
            abort(404, 'Boletín no encontrado');
        }

        $data = $request->validate([
            'titulo_pagina' => ['nullable', 'string', 'max:200'],
            'titulo_boletin' => ['sometimes', 'required', 'string', 'max:200'],
            'descripcion_boletin' => ['nullable', 'string'],
            'estado' => ['nullable', 'integer'],
        ]);
        DB::table('t_boletin')->where('id_boletin', $id)->update($data);

        return response()->json(DB::table('t_boletin')->where('id_boletin', $id)->first());
    }

    public function destroy(int $id): JsonResponse
    {
        DB::table('t_boletin')->where('id_boletin', $id)->update(['estado' => 0]);

        return response()->json(null, 204);
    }
}
