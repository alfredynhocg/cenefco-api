<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FotoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = $request->get('query', '');
        $size = (int) $request->get('pageSize', 50);
        $page = (int) $request->get('pageIndex', 1);

        $q = DB::table('t_foto');
        if ($query) {
            $q->where('titulo_foto', 'like', "%{$query}%");
        }
        if (! $request->boolean('conInactivos', false)) {
            $q->where('estado', 1);
        }

        $total = $q->count();
        $data = $q->orderByDesc('fecha_foto')->offset(($page - 1) * $size)->limit($size)->get();

        return response()->json(['data' => $data, 'total' => $total]);
    }

    public function show(int $id): JsonResponse
    {
        $row = DB::table('t_foto')->where('id_foto', $id)->first();
        if (! $row) {
            abort(404, 'Foto no encontrada');
        }

        return response()->json($row);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_foto' => ['required', 'integer'],
            'id_us_reg' => ['nullable', 'integer'],
            'num_foto' => ['nullable', 'integer'],
            'titulo_foto' => ['required', 'string', 'max:200'],
            'descripcion_foto' => ['nullable', 'string'],
            'foto' => ['nullable', 'string', 'max:200'],
            'fecha_foto' => ['nullable', 'date'],
            'estado' => ['nullable', 'integer'],
        ]);
        $data['id_us_reg'] = $request->integer('id_us_reg', 0);
        $data['num_foto'] = $request->integer('num_foto', 0);
        $data['estado'] = $request->integer('estado', 1);
        $data['fecha_reg'] = now();

        DB::table('t_foto')->insert($data);

        return response()->json(DB::table('t_foto')->where('id_foto', $data['id_foto'])->first(), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = DB::table('t_foto')->where('id_foto', $id)->first();
        if (! $row) {
            abort(404, 'Foto no encontrada');
        }

        $data = $request->validate([
            'titulo_foto' => ['sometimes', 'required', 'string', 'max:200'],
            'descripcion_foto' => ['nullable', 'string'],
            'foto' => ['nullable', 'string', 'max:200'],
            'fecha_foto' => ['nullable', 'date'],
            'estado' => ['nullable', 'integer'],
        ]);
        DB::table('t_foto')->where('id_foto', $id)->update($data);

        return response()->json(DB::table('t_foto')->where('id_foto', $id)->first());
    }

    public function destroy(int $id): JsonResponse
    {
        DB::table('t_foto')->where('id_foto', $id)->update(['estado' => 0]);

        return response()->json(null, 204);
    }
}
