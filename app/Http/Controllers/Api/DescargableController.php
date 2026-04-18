<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DescargableController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = $request->get('query', '');
        $size = (int) $request->get('pageSize', 20);
        $page = (int) $request->get('pageIndex', 1);

        $q = DB::table('web_descargable');
        if ($query) {
            $q->where('nombre', 'like', "%{$query}%");
        }
        if ($request->has('tipo')) {
            $q->where('tipo', $request->get('tipo'));
        }
        if ($request->has('programa_id')) {
            $q->where('programa_id', (int) $request->get('programa_id'));
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
        $row = DB::table('web_descargable')->find($id);
        if (! $row) {
            abort(404, 'Descargable no encontrado');
        }

        return response()->json($row);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:300'],
            'tipo' => ['nullable', 'string', 'max:100'],
            'archivo_url' => ['required', 'string', 'max:500'],
            'imagen_portada_url' => ['nullable', 'string', 'max:255'],
            'programa_id' => ['nullable', 'integer'],
            'requiere_datos' => ['nullable', 'boolean'],
            'orden' => ['nullable', 'integer'],
            'activo' => ['nullable', 'boolean'],
        ]);
        $data['requiere_datos'] = $request->boolean('requiere_datos', true);
        $data['descargas'] = 0;
        $data['orden'] = $request->integer('orden', 0);
        $data['activo'] = $request->boolean('activo', true);
        $data['created_at'] = now();

        $id = DB::table('web_descargable')->insertGetId($data);
        $row = DB::table('web_descargable')->find($id);

        return response()->json($row, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = DB::table('web_descargable')->find($id);
        if (! $row) {
            abort(404, 'Descargable no encontrado');
        }

        $data = $request->validate([
            'nombre' => ['sometimes', 'required', 'string', 'max:300'],
            'tipo' => ['nullable', 'string', 'max:100'],
            'archivo_url' => ['sometimes', 'required', 'string', 'max:500'],
            'imagen_portada_url' => ['nullable', 'string', 'max:255'],
            'programa_id' => ['nullable', 'integer'],
            'requiere_datos' => ['nullable', 'boolean'],
            'orden' => ['nullable', 'integer'],
            'activo' => ['nullable', 'boolean'],
        ]);
        $data['updated_at'] = now();
        DB::table('web_descargable')->where('id', $id)->update($data);

        return response()->json(DB::table('web_descargable')->find($id));
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = DB::table('web_descargable')->where('id', $id)->delete();
        if (! $deleted) {
            abort(404, 'Descargable no encontrado');
        }

        return response()->json(null, 204);
    }
}
