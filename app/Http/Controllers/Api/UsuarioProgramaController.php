<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UsuarioProgramaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $size = (int) $request->get('pageSize', 50);
        $page = (int) $request->get('pageIndex', 1);

        $q = DB::table('t_usuarioprograma');
        if ($request->has('id_us')) {
            $q->where('id_us', (int) $request->get('id_us'));
        }
        if ($request->has('id_programa')) {
            $q->where('id_programa', (int) $request->get('id_programa'));
        }
        if ($request->has('id_tipoprograma')) {
            $q->where('id_tipoprograma', (int) $request->get('id_tipoprograma'));
        }
        if (! $request->boolean('conInactivos', false)) {
            $q->where('estado', 1);
        }

        $total = $q->count();
        $data = $q->orderBy('id_usuarioprograma')->offset(($page - 1) * $size)->limit($size)->get();

        return response()->json(['data' => $data, 'total' => $total]);
    }

    public function show(int $id): JsonResponse
    {
        $row = DB::table('t_usuarioprograma')->where('id_usuarioprograma', $id)->first();
        if (! $row) {
            abort(404, 'Usuario-Programa no encontrado');
        }

        return response()->json($row);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_usuarioprograma' => ['required', 'integer'],
            'id_us' => ['required', 'integer'],
            'id_us_reg' => ['nullable', 'integer'],
            'num_usuarioprograma' => ['nullable', 'integer'],
            'id_programa' => ['nullable', 'integer'],
            'id_tipoprograma' => ['nullable', 'integer'],
            'estado' => ['nullable', 'integer'],
        ]);
        $data['id_us_reg'] = $request->integer('id_us_reg', 0);
        $data['num_usuarioprograma'] = $request->integer('num_usuarioprograma', 0);
        $data['estado'] = $request->integer('estado', 1);
        $data['fecha_reg'] = now();

        DB::table('t_usuarioprograma')->insert($data);

        return response()->json(DB::table('t_usuarioprograma')->where('id_usuarioprograma', $data['id_usuarioprograma'])->first(), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = DB::table('t_usuarioprograma')->where('id_usuarioprograma', $id)->first();
        if (! $row) {
            abort(404, 'Usuario-Programa no encontrado');
        }

        $data = $request->validate([
            'id_programa' => ['nullable', 'integer'],
            'id_tipoprograma' => ['nullable', 'integer'],
            'estado' => ['nullable', 'integer'],
        ]);
        DB::table('t_usuarioprograma')->where('id_usuarioprograma', $id)->update($data);

        return response()->json(DB::table('t_usuarioprograma')->where('id_usuarioprograma', $id)->first());
    }

    public function destroy(int $id): JsonResponse
    {
        DB::table('t_usuarioprograma')->where('id_usuarioprograma', $id)->update(['estado' => 0]);

        return response()->json(null, 204);
    }
}
