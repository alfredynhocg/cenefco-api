<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UsuarioPlanController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $size = (int) $request->get('pageSize', 50);
        $page = (int) $request->get('pageIndex', 1);

        $q = DB::table('t_usuarioplan');
        if ($request->has('id_us')) {
            $q->where('id_us', (int) $request->get('id_us'));
        }
        if ($request->has('id_plan')) {
            $q->where('id_plan', (int) $request->get('id_plan'));
        }
        if (! $request->boolean('conInactivos', false)) {
            $q->where('estado', 1);
        }

        $total = $q->count();
        $data = $q->orderBy('id_usuarioplan')->offset(($page - 1) * $size)->limit($size)->get();

        return response()->json(['data' => $data, 'total' => $total]);
    }

    public function show(int $id): JsonResponse
    {
        $row = DB::table('t_usuarioplan')->where('id_usuarioplan', $id)->first();
        if (! $row) {
            abort(404, 'Usuario-Plan no encontrado');
        }

        return response()->json($row);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_usuarioplan' => ['required', 'integer'],
            'id_us' => ['required', 'integer'],
            'id_us_reg' => ['nullable', 'integer'],
            'num_usuarioplan' => ['nullable', 'integer'],
            'id_plan' => ['nullable', 'integer'],
            'estado' => ['nullable', 'integer'],
        ]);
        $data['id_us_reg'] = $request->integer('id_us_reg', 0);
        $data['num_usuarioplan'] = $request->integer('num_usuarioplan', 0);
        $data['estado'] = $request->integer('estado', 1);
        $data['fecha_reg'] = now();

        DB::table('t_usuarioplan')->insert($data);

        return response()->json(DB::table('t_usuarioplan')->where('id_usuarioplan', $data['id_usuarioplan'])->first(), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = DB::table('t_usuarioplan')->where('id_usuarioplan', $id)->first();
        if (! $row) {
            abort(404, 'Usuario-Plan no encontrado');
        }

        $data = $request->validate([
            'id_plan' => ['nullable', 'integer'],
            'estado' => ['nullable', 'integer'],
        ]);
        DB::table('t_usuarioplan')->where('id_usuarioplan', $id)->update($data);

        return response()->json(DB::table('t_usuarioplan')->where('id_usuarioplan', $id)->first());
    }

    public function destroy(int $id): JsonResponse
    {
        DB::table('t_usuarioplan')->where('id_usuarioplan', $id)->update(['estado' => 0]);

        return response()->json(null, 204);
    }
}
