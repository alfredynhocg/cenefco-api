<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GaleriaCategoriaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = $request->get('query', '');
        $size = (int) $request->get('pageSize', 50);
        $page = (int) $request->get('pageIndex', 1);

        $q = DB::table('web_galeria_categoria');
        if ($query) {
            $q->where('nombre', 'like', "%{$query}%");
        }
        if ($request->boolean('soloActivos', false)) {
            $q->where('activo', true);
        }

        $total = $q->count();
        $data = $q->orderBy('orden')->orderBy('nombre')->offset(($page - 1) * $size)->limit($size)->get();

        return response()->json(['data' => $data, 'total' => $total]);
    }

    public function show(int $id): JsonResponse
    {
        $row = DB::table('web_galeria_categoria')->find($id);
        if (! $row) {
            abort(404, 'Categoría de galería no encontrada');
        }

        return response()->json($row);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:200'],
            'slug' => ['nullable', 'string', 'max:200', 'unique:web_galeria_categoria,slug'],
            'descripcion' => ['nullable', 'string'],
            'orden' => ['nullable', 'integer'],
            'activo' => ['nullable', 'boolean'],
        ]);
        $data['slug'] = $data['slug'] ?? Str::slug($data['nombre']);
        $data['orden'] = $request->integer('orden', 0);
        $data['activo'] = $request->boolean('activo', true);
        $data['created_at'] = now();

        $id = DB::table('web_galeria_categoria')->insertGetId($data);
        $row = DB::table('web_galeria_categoria')->find($id);

        return response()->json($row, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = DB::table('web_galeria_categoria')->find($id);
        if (! $row) {
            abort(404, 'Categoría de galería no encontrada');
        }

        $data = $request->validate([
            'nombre' => ['sometimes', 'required', 'string', 'max:200'],
            'slug' => ['nullable', 'string', 'max:200'],
            'descripcion' => ['nullable', 'string'],
            'orden' => ['nullable', 'integer'],
            'activo' => ['nullable', 'boolean'],
        ]);
        $data['updated_at'] = now();
        DB::table('web_galeria_categoria')->where('id', $id)->update($data);

        return response()->json(DB::table('web_galeria_categoria')->find($id));
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = DB::table('web_galeria_categoria')->where('id', $id)->delete();
        if (! $deleted) {
            abort(404, 'Categoría de galería no encontrada');
        }

        return response()->json(null, 204);
    }
}
