<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PagoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $size = (int) $request->get('pageSize', 30);
        $page = (int) $request->get('pageIndex', 1);

        $q = DB::table('t_pago');
        if ($request->has('id_us')) {
            $q->where('id_us', (int) $request->get('id_us'));
        }
        if ($request->has('id_fechapago')) {
            $q->where('id_fechapago', (int) $request->get('id_fechapago'));
        }
        if ($request->has('tipo_fechapago')) {
            $q->where('tipo_fechapago', (int) $request->get('tipo_fechapago'));
        }
        if (! $request->boolean('conInactivos', false)) {
            $q->where('estado', 1);
        }

        $total = $q->count();
        $data = $q->orderByDesc('fecha_deposito')->offset(($page - 1) * $size)->limit($size)->get();

        return response()->json(['data' => $data, 'total' => $total]);
    }

    public function show(int $id): JsonResponse
    {
        $row = DB::table('t_pago')->where('id_pago', $id)->first();
        if (! $row) {
            abort(404, 'Pago no encontrado');
        }

        return response()->json($row);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_pago' => ['required', 'integer'],
            'id_us_reg' => ['nullable', 'integer'],
            'id_us' => ['required', 'integer'],
            'id_mat' => ['nullable', 'integer'],
            'id_fechapago' => ['nullable', 'integer'],
            'monto_pagado' => ['nullable', 'string', 'max:200'],
            'nro_boleta_bancaria' => ['nullable', 'string', 'max:200'],
            'fecha_deposito' => ['nullable', 'date'],
            'nro_nit' => ['nullable', 'string', 'max:200'],
            'nombre_nit' => ['nullable', 'string', 'max:200'],
            'tipo_fechapago' => ['nullable', 'integer'],
            'observacion_pago' => ['nullable', 'string'],
            'estado' => ['nullable', 'integer'],
        ]);
        $data['id_us_reg'] = $request->integer('id_us_reg', 0);
        $data['estado'] = $request->integer('estado', 1);
        $data['fecha_reg'] = now();

        DB::table('t_pago')->insert($data);

        return response()->json(DB::table('t_pago')->where('id_pago', $data['id_pago'])->first(), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = DB::table('t_pago')->where('id_pago', $id)->first();
        if (! $row) {
            abort(404, 'Pago no encontrado');
        }

        $data = $request->validate([
            'monto_pagado' => ['nullable', 'string', 'max:200'],
            'nro_boleta_bancaria' => ['nullable', 'string', 'max:200'],
            'fecha_deposito' => ['nullable', 'date'],
            'observacion_pago' => ['nullable', 'string'],
            'estado' => ['nullable', 'integer'],
        ]);
        DB::table('t_pago')->where('id_pago', $id)->update($data);

        return response()->json(DB::table('t_pago')->where('id_pago', $id)->first());
    }

    public function destroy(int $id): JsonResponse
    {
        DB::table('t_pago')->where('id_pago', $id)->update(['estado' => 0]);

        return response()->json(null, 204);
    }

    /** Stripe webhook placeholder — implement as needed */
    public function webhook(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse
    {
        return response()->json(['received' => true]);
    }
}
