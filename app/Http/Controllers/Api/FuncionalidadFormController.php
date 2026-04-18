<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FuncionalidadFormController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $size = (int) $request->get('pageSize', 50);
        $page = (int) $request->get('pageIndex', 1);

        $q = DB::table('t_funcionalidadform');
        if ($request->has('id_regform')) {
            $q->where('id_regform', (int) $request->get('id_regform'));
        }
        if (! $request->boolean('conInactivos', false)) {
            $q->where('estado', 1);
        }

        $total = $q->count();
        $data = $q->orderBy('nombre_funcionalidad')->offset(($page - 1) * $size)->limit($size)->get();

        return response()->json(['data' => $data, 'total' => $total]);
    }

    public function show(int $id): JsonResponse
    {
        $row = DB::table('t_funcionalidadform')->where('id_funcionalidadform', $id)->first();
        if (! $row) {
            abort(404, 'Funcionalidad no encontrada');
        }

        return response()->json($row);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_funcionalidadform' => ['required', 'integer'],
            'id_regform' => ['required', 'integer'],
            'id_us_reg' => ['nullable', 'integer'],
            'num_funcionalidadform' => ['nullable', 'integer'],
            'codigo_funcionalidad' => ['nullable', 'string', 'max:200'],
            'nombre_funcionalidad' => ['required', 'string', 'max:200'],
            'estado' => ['nullable', 'integer'],
        ]);
        $data['id_us_reg'] = $request->integer('id_us_reg', 0);
        $data['num_funcionalidadform'] = $request->integer('num_funcionalidadform', 0);
        $data['estado'] = $request->integer('estado', 1);
        $data['fecha_reg'] = now();

        DB::table('t_funcionalidadform')->insert($data);

        return response()->json(DB::table('t_funcionalidadform')->where('id_funcionalidadform', $data['id_funcionalidadform'])->first(), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = DB::table('t_funcionalidadform')->where('id_funcionalidadform', $id)->first();
        if (! $row) {
            abort(404, 'Funcionalidad no encontrada');
        }

        $data = $request->validate([
            'codigo_funcionalidad' => ['nullable', 'string', 'max:200'],
            'nombre_funcionalidad' => ['sometimes', 'required', 'string', 'max:200'],
            'id_regform' => ['nullable', 'integer'],
            'estado' => ['nullable', 'integer'],
        ]);
        DB::table('t_funcionalidadform')->where('id_funcionalidadform', $id)->update($data);

        return response()->json(DB::table('t_funcionalidadform')->where('id_funcionalidadform', $id)->first());
    }

    public function destroy(int $id): JsonResponse
    {
        DB::table('t_funcionalidadform')->where('id_funcionalidadform', $id)->update(['estado' => 0]);

        return response()->json(null, 204);
    }
}
