<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CarreraController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = $request->get('query', '');
        $size = (int) $request->get('pageSize', 50);
        $page = (int) $request->get('pageIndex', 1);

        $q = DB::table('t_carrera');
        if ($query) {
            $q->where('nombre_carrera', 'like', "%{$query}%");
        }
        if (! $request->boolean('conInactivos', false)) {
            $q->where('estado', 1);
        }

        $total = $q->count();
        $data = $q->orderBy('nombre_carrera')->offset(($page - 1) * $size)->limit($size)->get();

        return response()->json(['data' => $data, 'total' => $total]);
    }

    public function show(int $id): JsonResponse
    {
        $row = DB::table('t_carrera')->where('id_carrera', $id)->first();
        if (! $row) {
            abort(404, 'Carrera no encontrada');
        }

        return response()->json($row);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_carrera' => ['required', 'integer'],
            'id_us_reg' => ['nullable', 'integer'],
            'num_carrera' => ['nullable', 'integer'],
            'nombre_carrera' => ['required', 'string', 'max:200'],
            'estado' => ['nullable', 'integer'],
        ]);
        $data['id_us_reg'] = $request->integer('id_us_reg', 0);
        $data['num_carrera'] = $request->integer('num_carrera', 0);
        $data['estado'] = $request->integer('estado', 1);
        $data['fecha_reg'] = now();

        DB::table('t_carrera')->insert($data);

        return response()->json(DB::table('t_carrera')->where('id_carrera', $data['id_carrera'])->first(), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = DB::table('t_carrera')->where('id_carrera', $id)->first();
        if (! $row) {
            abort(404, 'Carrera no encontrada');
        }

        $data = $request->validate([
            'nombre_carrera' => ['sometimes', 'required', 'string', 'max:200'],
            'estado' => ['nullable', 'integer'],
        ]);
        DB::table('t_carrera')->where('id_carrera', $id)->update($data);

        return response()->json(DB::table('t_carrera')->where('id_carrera', $id)->first());
    }

    public function destroy(int $id): JsonResponse
    {
        DB::table('t_carrera')->where('id_carrera', $id)->update(['estado' => 0]);

        return response()->json(null, 204);
    }
}
