<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FechaPagoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $size = (int) $request->get('pageSize', 50);
        $page = (int) $request->get('pageIndex', 1);

        $q = DB::table('t_fechapago');
        if ($request->has('id_plan')) {
            $q->where('id_plan', (int) $request->get('id_plan'));
        }
        if ($request->has('id_tipopago')) {
            $q->where('id_tipopago', (int) $request->get('id_tipopago'));
        }
        if ($request->has('tipo_fechapago')) {
            $q->where('tipo_fechapago', (int) $request->get('tipo_fechapago'));
        }
        if (! $request->boolean('conInactivos', false)) {
            $q->where('estado', 1);
        }

        $total = $q->count();
        $data = $q->orderBy('fecha_inicio')->offset(($page - 1) * $size)->limit($size)->get();

        return response()->json(['data' => $data, 'total' => $total]);
    }

    public function show(int $id): JsonResponse
    {
        $row = DB::table('t_fechapago')->where('id_fechapago', $id)->first();
        if (! $row) {
            abort(404, 'Fecha de pago no encontrada');
        }

        return response()->json($row);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_fechapago' => ['required', 'integer'],
            'id_plan' => ['required', 'integer'],
            'id_us_reg' => ['nullable', 'integer'],
            'num_fechapago' => ['nullable', 'integer'],
            'nro_pago' => ['nullable', 'string', 'max:200'],
            'id_tipopago' => ['nullable', 'integer'],
            'tipo_tramite' => ['nullable', 'string', 'max:200'],
            'monto_a_pagar' => ['nullable', 'string', 'max:200'],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date'],
            'obligatorio' => ['nullable', 'integer'],
            'tipo_fechapago' => ['nullable', 'integer'],
            'estado' => ['nullable', 'integer'],
        ]);
        $data['id_us_reg'] = $request->integer('id_us_reg', 0);
        $data['num_fechapago'] = $request->integer('num_fechapago', 0);
        $data['estado'] = $request->integer('estado', 1);
        $data['fecha_reg'] = now();

        DB::table('t_fechapago')->insert($data);

        return response()->json(DB::table('t_fechapago')->where('id_fechapago', $data['id_fechapago'])->first(), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = DB::table('t_fechapago')->where('id_fechapago', $id)->first();
        if (! $row) {
            abort(404, 'Fecha de pago no encontrada');
        }

        $data = $request->validate([
            'nro_pago' => ['nullable', 'string', 'max:200'],
            'id_tipopago' => ['nullable', 'integer'],
            'tipo_tramite' => ['nullable', 'string', 'max:200'],
            'monto_a_pagar' => ['nullable', 'string', 'max:200'],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date'],
            'obligatorio' => ['nullable', 'integer'],
            'tipo_fechapago' => ['nullable', 'integer'],
            'estado' => ['nullable', 'integer'],
        ]);
        DB::table('t_fechapago')->where('id_fechapago', $id)->update($data);

        return response()->json(DB::table('t_fechapago')->where('id_fechapago', $id)->first());
    }

    public function destroy(int $id): JsonResponse
    {
        DB::table('t_fechapago')->where('id_fechapago', $id)->update(['estado' => 0]);

        return response()->json(null, 204);
    }
}
