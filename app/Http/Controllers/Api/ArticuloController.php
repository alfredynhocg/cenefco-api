<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ArticuloController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = $request->get('query', '');
        $size = (int) $request->get('pageSize', 30);
        $page = (int) $request->get('pageIndex', 1);

        $q = DB::table('t_articulo');
        if ($query) {
            $q->where('titulo', 'like', "%{$query}%");
        }
        if ($request->has('id_cat_art')) {
            $q->where('id_cat_art', (int) $request->get('id_cat_art'));
        }
        if (! $request->boolean('conInactivos', false)) {
            $q->where('estado', 1);
        }

        $total = $q->count();
        $data = $q->orderByDesc('id_art')->offset(($page - 1) * $size)->limit($size)->get();

        return response()->json(['data' => $data, 'total' => $total]);
    }

    public function show(int $id): JsonResponse
    {
        $row = DB::table('t_articulo')->where('id_art', $id)->first();
        if (! $row) {
            abort(404, 'Artículo no encontrado');
        }

        return response()->json($row);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_art' => ['required', 'integer'],
            'id_us_reg' => ['nullable', 'integer'],
            'num_art' => ['nullable', 'integer'],
            'titulo' => ['required', 'string', 'max:200'],
            'autor' => ['nullable', 'integer'],
            'contenido' => ['nullable', 'string'],
            'id_cat_art' => ['nullable', 'integer'],
            'estado' => ['nullable', 'integer'],
        ]);
        $data['id_us_reg'] = $request->integer('id_us_reg', 0);
        $data['num_art'] = $request->integer('num_art', 0);
        $data['estado'] = $request->integer('estado', 1);
        $data['fecha_reg'] = now();

        DB::table('t_articulo')->insert($data);

        return response()->json(DB::table('t_articulo')->where('id_art', $data['id_art'])->first(), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = DB::table('t_articulo')->where('id_art', $id)->first();
        if (! $row) {
            abort(404, 'Artículo no encontrado');
        }

        $data = $request->validate([
            'titulo' => ['sometimes', 'required', 'string', 'max:200'],
            'autor' => ['nullable', 'integer'],
            'contenido' => ['nullable', 'string'],
            'id_cat_art' => ['nullable', 'integer'],
            'estado' => ['nullable', 'integer'],
        ]);
        DB::table('t_articulo')->where('id_art', $id)->update($data);

        return response()->json(DB::table('t_articulo')->where('id_art', $id)->first());
    }

    public function destroy(int $id): JsonResponse
    {
        DB::table('t_articulo')->where('id_art', $id)->update(['estado' => 0]);

        return response()->json(null, 204);
    }
}
