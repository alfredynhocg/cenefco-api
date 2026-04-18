<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BloqueAjustableController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $size = (int) $request->get('pageSize', 50);
        $page = (int) $request->get('pageIndex', 1);

        $q = DB::table('t_bloqueajustable');
        if ($request->has('id_pagina')) {
            $q->where('id_pagina', (int) $request->get('id_pagina'));
        }
        if ($request->has('id_bloqueplantilla')) {
            $q->where('id_bloqueplantilla', (int) $request->get('id_bloqueplantilla'));
        }
        if (! $request->boolean('conInactivos', false)) {
            $q->where('estado', 1);
        }

        $total = $q->count();
        $data = $q->orderBy('id_bloqueajustable')->offset(($page - 1) * $size)->limit($size)->get();

        return response()->json(['data' => $data, 'total' => $total]);
    }

    public function show(int $id): JsonResponse
    {
        $row = DB::table('t_bloqueajustable')->where('id_bloqueajustable', $id)->first();
        if (! $row) {
            abort(404, 'Bloque ajustable no encontrado');
        }

        return response()->json($row);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_bloqueajustable' => ['required', 'integer'],
            'id_us_reg' => ['nullable', 'integer'],
            'num_bloqueajustable' => ['nullable', 'integer'],
            'id_pagina' => ['nullable', 'integer'],
            'id_bloqueplantilla' => ['nullable', 'integer'],
            'referencia_id_bloqueajustable' => ['nullable', 'string', 'max:200'],
            'nombre_bloqueajustable' => ['nullable', 'string', 'max:200'],
            'permitir_agregarseccion' => ['nullable', 'integer'],
            'numero_secciones' => ['nullable', 'string', 'max:200'],
            'permitir_configurarbloque' => ['nullable', 'integer'],
            'permitir_gestionarseccion' => ['nullable', 'integer'],
            'bd_tabla' => ['nullable', 'string', 'max:200'],
            'bd_campos' => ['nullable', 'string'],
            'bd_condicion' => ['nullable', 'string', 'max:200'],
            'permitir_configurarseccion' => ['nullable', 'integer'],
            'texto_bloque' => ['nullable', 'string'],
            'estado' => ['nullable', 'integer'],
        ]);
        $data['id_us_reg'] = $request->integer('id_us_reg', 0);
        $data['num_bloqueajustable'] = $request->integer('num_bloqueajustable', 0);
        $data['estado'] = $request->integer('estado', 1);
        $data['fecha_reg'] = now();

        DB::table('t_bloqueajustable')->insert($data);

        return response()->json(DB::table('t_bloqueajustable')->where('id_bloqueajustable', $data['id_bloqueajustable'])->first(), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = DB::table('t_bloqueajustable')->where('id_bloqueajustable', $id)->first();
        if (! $row) {
            abort(404, 'Bloque ajustable no encontrado');
        }

        $data = $request->validate([
            'nombre_bloqueajustable' => ['nullable', 'string', 'max:200'],
            'permitir_agregarseccion' => ['nullable', 'integer'],
            'numero_secciones' => ['nullable', 'string', 'max:200'],
            'permitir_configurarbloque' => ['nullable', 'integer'],
            'permitir_gestionarseccion' => ['nullable', 'integer'],
            'bd_tabla' => ['nullable', 'string', 'max:200'],
            'bd_campos' => ['nullable', 'string'],
            'bd_condicion' => ['nullable', 'string', 'max:200'],
            'permitir_configurarseccion' => ['nullable', 'integer'],
            'texto_bloque' => ['nullable', 'string'],
            'estado' => ['nullable', 'integer'],
        ]);
        DB::table('t_bloqueajustable')->where('id_bloqueajustable', $id)->update($data);

        return response()->json(DB::table('t_bloqueajustable')->where('id_bloqueajustable', $id)->first());
    }

    public function destroy(int $id): JsonResponse
    {
        DB::table('t_bloqueajustable')->where('id_bloqueajustable', $id)->update(['estado' => 0]);

        return response()->json(null, 204);
    }
}
