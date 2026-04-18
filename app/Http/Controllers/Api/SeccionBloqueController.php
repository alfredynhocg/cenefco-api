<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SeccionBloqueController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $size = (int) $request->get('pageSize', 50);
        $page = (int) $request->get('pageIndex', 1);

        $q = DB::table('t_seccionbloque');
        if ($request->has('id_bloqueajustable')) {
            $q->where('id_bloqueajustable', (int) $request->get('id_bloqueajustable'));
        }
        if (! $request->boolean('conInactivos', false)) {
            $q->where('estado', 1);
        }

        $total = $q->count();
        $data = $q->orderBy('id_seccionbloque')->offset(($page - 1) * $size)->limit($size)->get();

        return response()->json(['data' => $data, 'total' => $total]);
    }

    public function show(int $id): JsonResponse
    {
        $row = DB::table('t_seccionbloque')->where('id_seccionbloque', $id)->first();
        if (! $row) {
            abort(404, 'Sección de bloque no encontrada');
        }

        return response()->json($row);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_seccionbloque' => ['required', 'integer'],
            'id_bloqueajustable' => ['required', 'integer'],
            'id_us_reg' => ['nullable', 'integer'],
            'num_seccionbloque' => ['nullable', 'integer'],
            'texto_seccion' => ['nullable', 'string'],
            'estado' => ['nullable', 'integer'],
        ]);
        $data['id_us_reg'] = $request->integer('id_us_reg', 0);
        $data['num_seccionbloque'] = $request->integer('num_seccionbloque', 0);
        $data['estado'] = $request->integer('estado', 1);
        $data['fecha_reg'] = now();

        DB::table('t_seccionbloque')->insert($data);

        return response()->json(DB::table('t_seccionbloque')->where('id_seccionbloque', $data['id_seccionbloque'])->first(), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = DB::table('t_seccionbloque')->where('id_seccionbloque', $id)->first();
        if (! $row) {
            abort(404, 'Sección de bloque no encontrada');
        }

        $data = $request->validate([
            'texto_seccion' => ['nullable', 'string'],
            'id_bloqueajustable' => ['nullable', 'integer'],
            'estado' => ['nullable', 'integer'],
        ]);
        DB::table('t_seccionbloque')->where('id_seccionbloque', $id)->update($data);

        return response()->json(DB::table('t_seccionbloque')->where('id_seccionbloque', $id)->first());
    }

    public function destroy(int $id): JsonResponse
    {
        DB::table('t_seccionbloque')->where('id_seccionbloque', $id)->update(['estado' => 0]);

        return response()->json(null, 204);
    }
}
