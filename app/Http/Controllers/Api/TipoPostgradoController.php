<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TipoPostgradoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $size = (int) $request->get('pageSize', 50);
        $page = (int) $request->get('pageIndex', 1);

        $q = DB::table('t_tipopostgrado');
        if ($request->has('id_plan')) {
            $q->where('id_plan', (int) $request->get('id_plan'));
        }
        if ($request->has('id_tipopago')) {
            $q->where('id_tipopago', (int) $request->get('id_tipopago'));
        }
        if (! $request->boolean('conInactivos', false)) {
            $q->where('estado', 1);
        }

        $total = $q->count();
        $data = $q->orderBy('id_tipopost')->offset(($page - 1) * $size)->limit($size)->get();

        return response()->json(['data' => $data, 'total' => $total]);
    }

    public function show(int $id): JsonResponse
    {
        $row = DB::table('t_tipopostgrado')->where('id_tipopost', $id)->first();
        if (! $row) {
            abort(404, 'Tipo de postgrado no encontrado');
        }

        return response()->json($row);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_tipopost' => ['required', 'integer'],
            'id_plan' => ['required', 'integer'],
            'id_us_reg' => ['nullable', 'integer'],
            'num_tipopost' => ['nullable', 'integer'],
            'id_tipopago' => ['nullable', 'integer'],
            'descuentopostgrado' => ['nullable', 'string', 'max:200'],
            'calculo_cuota' => ['nullable', 'string', 'max:200'],
            'estado' => ['nullable', 'integer'],
        ]);
        $data['id_us_reg'] = $request->integer('id_us_reg', 0);
        $data['num_tipopost'] = $request->integer('num_tipopost', 0);
        $data['estado'] = $request->integer('estado', 1);
        $data['fecha_reg'] = now();

        DB::table('t_tipopostgrado')->insert($data);

        return response()->json(DB::table('t_tipopostgrado')->where('id_tipopost', $data['id_tipopost'])->first(), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = DB::table('t_tipopostgrado')->where('id_tipopost', $id)->first();
        if (! $row) {
            abort(404, 'Tipo de postgrado no encontrado');
        }

        $data = $request->validate([
            'id_tipopago' => ['nullable', 'integer'],
            'descuentopostgrado' => ['nullable', 'string', 'max:200'],
            'calculo_cuota' => ['nullable', 'string', 'max:200'],
            'estado' => ['nullable', 'integer'],
        ]);
        DB::table('t_tipopostgrado')->where('id_tipopost', $id)->update($data);

        return response()->json(DB::table('t_tipopostgrado')->where('id_tipopost', $id)->first());
    }

    public function destroy(int $id): JsonResponse
    {
        DB::table('t_tipopostgrado')->where('id_tipopost', $id)->update(['estado' => 0]);

        return response()->json(null, 204);
    }
}
