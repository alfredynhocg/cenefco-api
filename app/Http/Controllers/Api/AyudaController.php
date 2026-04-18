<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AyudaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $size = (int) $request->get('pageSize', 30);
        $page = (int) $request->get('pageIndex', 1);

        $q = DB::table('t_ayuda');
        if ($request->has('id_us')) {
            $q->where('id_us', (int) $request->get('id_us'));
        }
        if ($request->has('gestion')) {
            $q->where('gestion', $request->get('gestion'));
        }
        if (! $request->boolean('conInactivos', false)) {
            $q->where('estado', 1);
        }

        $total = $q->count();
        $data = $q->orderByDesc('fecha_recibo')->offset(($page - 1) * $size)->limit($size)->get();

        return response()->json(['data' => $data, 'total' => $total]);
    }

    public function show(int $id): JsonResponse
    {
        $row = DB::table('t_ayuda')->where('id_ayuda', $id)->first();
        if (! $row) {
            abort(404, 'Ayuda no encontrada');
        }

        return response()->json($row);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_ayuda' => ['required', 'integer'],
            'id_us_reg' => ['nullable', 'integer'],
            'num_ayuda' => ['nullable', 'integer'],
            'id_us' => ['required', 'integer'],
            'gestion' => ['nullable', 'string', 'max:200'],
            'monto_pagado' => ['nullable', 'string', 'max:200'],
            'nro_recibo' => ['nullable', 'string', 'max:200'],
            'fecha_recibo' => ['nullable', 'date'],
            'observacion_pago' => ['nullable', 'string'],
            'id_categoriatipoayuda' => ['nullable', 'integer'],
            'estado' => ['nullable', 'integer'],
        ]);
        $data['id_us_reg'] = $request->integer('id_us_reg', 0);
        $data['num_ayuda'] = $request->integer('num_ayuda', 0);
        $data['estado'] = $request->integer('estado', 1);
        $data['fecha_reg'] = now();

        DB::table('t_ayuda')->insert($data);

        return response()->json(DB::table('t_ayuda')->where('id_ayuda', $data['id_ayuda'])->first(), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = DB::table('t_ayuda')->where('id_ayuda', $id)->first();
        if (! $row) {
            abort(404, 'Ayuda no encontrada');
        }

        $data = $request->validate([
            'gestion' => ['nullable', 'string', 'max:200'],
            'monto_pagado' => ['nullable', 'string', 'max:200'],
            'nro_recibo' => ['nullable', 'string', 'max:200'],
            'fecha_recibo' => ['nullable', 'date'],
            'observacion_pago' => ['nullable', 'string'],
            'id_categoriatipoayuda' => ['nullable', 'integer'],
            'estado' => ['nullable', 'integer'],
        ]);
        DB::table('t_ayuda')->where('id_ayuda', $id)->update($data);

        return response()->json(DB::table('t_ayuda')->where('id_ayuda', $id)->first());
    }

    public function destroy(int $id): JsonResponse
    {
        DB::table('t_ayuda')->where('id_ayuda', $id)->update(['estado' => 0]);

        return response()->json(null, 204);
    }
}
