<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RedireccionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = $request->get('query', '');
        $size = (int) $request->get('pageSize', 30);
        $page = (int) $request->get('pageIndex', 1);

        $q = DB::table('web_redireccion');
        if ($query) {
            $q->where(function ($sq) use ($query) {
                $sq->where('url_origen', 'like', "%{$query}%")
                    ->orWhere('url_destino', 'like', "%{$query}%");
            });
        }
        if ($request->has('activo')) {
            $q->where('activo', $request->boolean('activo'));
        }

        $total = $q->count();
        $data = $q->orderBy('url_origen')->offset(($page - 1) * $size)->limit($size)->get();

        return response()->json(['data' => $data, 'total' => $total]);
    }

    public function show(int $id): JsonResponse
    {
        $row = DB::table('web_redireccion')->find($id);
        if (! $row) {
            abort(404, 'Redirección no encontrada');
        }

        return response()->json($row);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'url_origen' => ['required', 'string', 'max:500', 'unique:web_redireccion,url_origen'],
            'url_destino' => ['required', 'string', 'max:500'],
            'codigo_http' => ['nullable', 'integer', 'in:301,302'],
            'activo' => ['nullable', 'boolean'],
            'notas' => ['nullable', 'string', 'max:300'],
        ]);
        $data['codigo_http'] = $request->integer('codigo_http', 301);
        $data['hits'] = 0;
        $data['activo'] = $request->boolean('activo', true);
        $data['created_at'] = now();

        $id = DB::table('web_redireccion')->insertGetId($data);
        $row = DB::table('web_redireccion')->find($id);

        return response()->json($row, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = DB::table('web_redireccion')->find($id);
        if (! $row) {
            abort(404, 'Redirección no encontrada');
        }

        $data = $request->validate([
            'url_origen' => ['sometimes', 'required', 'string', 'max:500'],
            'url_destino' => ['sometimes', 'required', 'string', 'max:500'],
            'codigo_http' => ['nullable', 'integer', 'in:301,302'],
            'activo' => ['nullable', 'boolean'],
            'notas' => ['nullable', 'string', 'max:300'],
        ]);
        $data['updated_at'] = now();
        DB::table('web_redireccion')->where('id', $id)->update($data);

        return response()->json(DB::table('web_redireccion')->find($id));
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = DB::table('web_redireccion')->where('id', $id)->delete();
        if (! $deleted) {
            abort(404, 'Redirección no encontrada');
        }

        return response()->json(null, 204);
    }
}
