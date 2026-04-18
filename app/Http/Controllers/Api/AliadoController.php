<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AliadoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = $request->get('query', '');
        $size = (int) $request->get('pageSize', 50);
        $page = (int) $request->get('pageIndex', 1);

        $q = DB::table('web_aliado');
        if ($query) {
            $q->where('nombre', 'like', "%{$query}%");
        }
        if ($request->has('tipo')) {
            $q->where('tipo', $request->get('tipo'));
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
        $row = DB::table('web_aliado')->find($id);
        if (! $row) {
            abort(404, 'Aliado no encontrado');
        }

        return response()->json($row);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:200'],
            'logo_url' => ['required', 'string', 'max:255'],
            'logo_alt' => ['nullable', 'string', 'max:255'],
            'url_sitio' => ['nullable', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'tipo' => ['nullable', 'string', 'max:100'],
            'orden' => ['nullable', 'integer'],
            'activo' => ['nullable', 'boolean'],
        ]);
        $data['orden'] = $request->integer('orden', 0);
        $data['activo'] = $request->boolean('activo', true);
        $data['created_at'] = now();

        $id = DB::table('web_aliado')->insertGetId($data);
        $row = DB::table('web_aliado')->find($id);

        return response()->json($row, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = DB::table('web_aliado')->find($id);
        if (! $row) {
            abort(404, 'Aliado no encontrado');
        }

        $data = $request->validate([
            'nombre' => ['sometimes', 'required', 'string', 'max:200'],
            'logo_url' => ['sometimes', 'required', 'string', 'max:255'],
            'logo_alt' => ['nullable', 'string', 'max:255'],
            'url_sitio' => ['nullable', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'tipo' => ['nullable', 'string', 'max:100'],
            'orden' => ['nullable', 'integer'],
            'activo' => ['nullable', 'boolean'],
        ]);
        $data['updated_at'] = now();
        DB::table('web_aliado')->where('id', $id)->update($data);

        return response()->json(DB::table('web_aliado')->find($id));
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = DB::table('web_aliado')->where('id', $id)->delete();
        if (! $deleted) {
            abort(404, 'Aliado no encontrado');
        }

        return response()->json(null, 204);
    }
}
