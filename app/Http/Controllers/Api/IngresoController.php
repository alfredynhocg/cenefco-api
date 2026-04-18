<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IngresoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $size = (int) $request->get('pageSize', 50);
        $page = (int) $request->get('pageIndex', 1);

        $q = DB::table('t_ingreso');
        if ($request->has('id_us_reg')) {
            $q->where('id_us_reg', (int) $request->get('id_us_reg'));
        }

        $total = $q->count();
        $data = $q->orderByDesc('fecha')->offset(($page - 1) * $size)->limit($size)->get();

        return response()->json(['data' => $data, 'total' => $total]);
    }

    public function show(float $id): JsonResponse
    {
        $row = DB::table('t_ingreso')->where('id_ing', $id)->first();
        if (! $row) {
            abort(404, 'Ingreso no encontrado');
        }

        return response()->json($row);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_ing' => ['required', 'numeric'],
            'id_us_reg' => ['nullable', 'integer'],
            'fecha' => ['nullable', 'date'],
        ]);
        $data['id_us_reg'] = $request->integer('id_us_reg', 0);

        DB::table('t_ingreso')->insert($data);

        return response()->json(DB::table('t_ingreso')->where('id_ing', $data['id_ing'])->first(), 201);
    }

    public function update(Request $request, float $id): JsonResponse
    {
        $row = DB::table('t_ingreso')->where('id_ing', $id)->first();
        if (! $row) {
            abort(404, 'Ingreso no encontrado');
        }

        $data = $request->validate([
            'fecha' => ['nullable', 'date'],
        ]);
        DB::table('t_ingreso')->where('id_ing', $id)->update($data);

        return response()->json(DB::table('t_ingreso')->where('id_ing', $id)->first());
    }

    public function destroy(float $id): JsonResponse
    {
        $row = DB::table('t_ingreso')->where('id_ing', $id)->first();
        if (! $row) {
            abort(404, 'Ingreso no encontrado');
        }
        DB::table('t_ingreso')->where('id_ing', $id)->delete();

        return response()->json(null, 204);
    }
}
