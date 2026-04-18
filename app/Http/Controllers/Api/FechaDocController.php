<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FechaDocController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $size = (int) $request->get('pageSize', 50);
        $page = (int) $request->get('pageIndex', 1);

        $q = DB::table('t_fechadoc');
        if ($request->has('id_plandoc')) {
            $q->where('id_plandoc', (int) $request->get('id_plandoc'));
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
        $row = DB::table('t_fechadoc')->where('id_fechadoc', $id)->first();
        if (! $row) {
            abort(404, 'Fecha de documento no encontrada');
        }

        return response()->json($row);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_fechadoc' => ['required', 'integer'],
            'id_plandoc' => ['required', 'integer'],
            'id_us_reg' => ['nullable', 'integer'],
            'num_fechadoc' => ['nullable', 'integer'],
            'nro_doc' => ['nullable', 'string', 'max:200'],
            'tipo_documento' => ['nullable', 'string', 'max:200'],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date'],
            'obligatorio' => ['nullable', 'integer'],
            'estado' => ['nullable', 'integer'],
        ]);
        $data['id_us_reg'] = $request->integer('id_us_reg', 0);
        $data['num_fechadoc'] = $request->integer('num_fechadoc', 0);
        $data['estado'] = $request->integer('estado', 1);
        $data['fecha_reg'] = now();

        DB::table('t_fechadoc')->insert($data);

        return response()->json(DB::table('t_fechadoc')->where('id_fechadoc', $data['id_fechadoc'])->first(), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = DB::table('t_fechadoc')->where('id_fechadoc', $id)->first();
        if (! $row) {
            abort(404, 'Fecha de documento no encontrada');
        }

        $data = $request->validate([
            'nro_doc' => ['nullable', 'string', 'max:200'],
            'tipo_documento' => ['nullable', 'string', 'max:200'],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date'],
            'obligatorio' => ['nullable', 'integer'],
            'estado' => ['nullable', 'integer'],
        ]);
        DB::table('t_fechadoc')->where('id_fechadoc', $id)->update($data);

        return response()->json(DB::table('t_fechadoc')->where('id_fechadoc', $id)->first());
    }

    public function destroy(int $id): JsonResponse
    {
        DB::table('t_fechadoc')->where('id_fechadoc', $id)->update(['estado' => 0]);

        return response()->json(null, 204);
    }
}
