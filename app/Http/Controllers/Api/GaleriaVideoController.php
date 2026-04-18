<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GaleriaVideoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = $request->get('query', '');
        $size = (int) $request->get('pageSize', 20);
        $page = (int) $request->get('pageIndex', 1);

        $q = DB::table('web_galeria_video');
        if ($query) {
            $q->where('titulo', 'like', "%{$query}%");
        }
        if ($request->has('tipo')) {
            $q->where('tipo', $request->get('tipo'));
        }
        if ($request->has('programa_id')) {
            $q->where('programa_id', (int) $request->get('programa_id'));
        }
        if ($request->boolean('soloDestacados', false)) {
            $q->where('destacado', true);
        }
        if ($request->boolean('soloActivos', false)) {
            $q->where('activo', true);
        }

        $total = $q->count();
        $data = $q->orderBy('orden')->orderByDesc('id')->offset(($page - 1) * $size)->limit($size)->get();

        return response()->json(['data' => $data, 'total' => $total]);
    }

    public function show(int $id): JsonResponse
    {
        $row = DB::table('web_galeria_video')->find($id);
        if (! $row) {
            abort(404, 'Video no encontrado');
        }

        return response()->json($row);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'titulo' => ['required', 'string', 'max:300'],
            'descripcion' => ['nullable', 'string'],
            'plataforma' => ['nullable', 'string', 'max:50'],
            'url_video' => ['required', 'string', 'max:500'],
            'video_id' => ['nullable', 'string', 'max:100'],
            'miniatura_url' => ['nullable', 'string', 'max:255'],
            'duracion' => ['nullable', 'string', 'max:20'],
            'tipo' => ['nullable', 'string', 'max:100'],
            'programa_id' => ['nullable', 'integer'],
            'destacado' => ['nullable', 'boolean'],
            'orden' => ['nullable', 'integer'],
            'activo' => ['nullable', 'boolean'],
        ]);
        $data['plataforma'] = $data['plataforma'] ?? 'youtube';
        $data['destacado'] = $request->boolean('destacado', false);
        $data['orden'] = $request->integer('orden', 0);
        $data['vistas'] = 0;
        $data['activo'] = $request->boolean('activo', true);
        $data['created_at'] = now();

        $id = DB::table('web_galeria_video')->insertGetId($data);
        $row = DB::table('web_galeria_video')->find($id);

        return response()->json($row, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = DB::table('web_galeria_video')->find($id);
        if (! $row) {
            abort(404, 'Video no encontrado');
        }

        $data = $request->validate([
            'titulo' => ['sometimes', 'required', 'string', 'max:300'],
            'descripcion' => ['nullable', 'string'],
            'plataforma' => ['nullable', 'string', 'max:50'],
            'url_video' => ['sometimes', 'required', 'string', 'max:500'],
            'video_id' => ['nullable', 'string', 'max:100'],
            'miniatura_url' => ['nullable', 'string', 'max:255'],
            'duracion' => ['nullable', 'string', 'max:20'],
            'tipo' => ['nullable', 'string', 'max:100'],
            'programa_id' => ['nullable', 'integer'],
            'destacado' => ['nullable', 'boolean'],
            'orden' => ['nullable', 'integer'],
            'activo' => ['nullable', 'boolean'],
        ]);
        $data['updated_at'] = now();
        DB::table('web_galeria_video')->where('id', $id)->update($data);

        return response()->json(DB::table('web_galeria_video')->find($id));
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = DB::table('web_galeria_video')->where('id', $id)->delete();
        if (! $deleted) {
            abort(404, 'Video no encontrado');
        }

        return response()->json(null, 204);
    }
}
