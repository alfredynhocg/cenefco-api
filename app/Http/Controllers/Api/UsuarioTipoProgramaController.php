<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UsuarioTipoProgramaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $size = (int) $request->get('pageSize', 50);
        $page = (int) $request->get('pageIndex', 1);

        $q = DB::table('t_usuariotipoprograma');
        if ($request->has('id_us')) {
            $q->where('id_us', (int) $request->get('id_us'));
        }
        if ($request->has('id_tipoprograma')) {
            $q->where('id_tipoprograma', (int) $request->get('id_tipoprograma'));
        }
        if (! $request->boolean('conInactivos', false)) {
            $q->where('estado', 1);
        }

        $total = $q->count();
        $data = $q->orderBy('id_usuariotipoprograma')->offset(($page - 1) * $size)->limit($size)->get();

        return response()->json(['data' => $data, 'total' => $total]);
    }

    public function show(int $id): JsonResponse
    {
        $row = DB::table('t_usuariotipoprograma')->where('id_usuariotipoprograma', $id)->first();
        if (! $row) {
            abort(404, 'Usuario-TipoPrograma no encontrado');
        }

        return response()->json($row);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_usuariotipoprograma' => ['required', 'integer'],
            'id_us' => ['required', 'integer'],
            'id_us_reg' => ['nullable', 'integer'],
            'num_usuariotipoprograma' => ['nullable', 'integer'],
            'id_tipoprograma' => ['nullable', 'integer'],
            'estado' => ['nullable', 'integer'],
        ]);
        $data['id_us_reg'] = $request->integer('id_us_reg', 0);
        $data['num_usuariotipoprograma'] = $request->integer('num_usuariotipoprograma', 0);
        $data['estado'] = $request->integer('estado', 1);
        $data['fecha_reg'] = now();

        DB::table('t_usuariotipoprograma')->insert($data);

        return response()->json(DB::table('t_usuariotipoprograma')->where('id_usuariotipoprograma', $data['id_usuariotipoprograma'])->first(), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = DB::table('t_usuariotipoprograma')->where('id_usuariotipoprograma', $id)->first();
        if (! $row) {
            abort(404, 'Usuario-TipoPrograma no encontrado');
        }

        $data = $request->validate([
            'id_tipoprograma' => ['nullable', 'integer'],
            'estado' => ['nullable', 'integer'],
        ]);
        DB::table('t_usuariotipoprograma')->where('id_usuariotipoprograma', $id)->update($data);

        return response()->json(DB::table('t_usuariotipoprograma')->where('id_usuariotipoprograma', $id)->first());
    }

    public function destroy(int $id): JsonResponse
    {
        DB::table('t_usuariotipoprograma')->where('id_usuariotipoprograma', $id)->update(['estado' => 0]);

        return response()->json(null, 204);
    }
}
