<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotaPrensaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = $request->get('query', '');
        $size = (int) $request->get('pageSize', 20);
        $page = (int) $request->get('pageIndex', 1);

        $q = DB::table('web_nota_prensa');
        if ($query) {
            $q->where(function ($sq) use ($query) {
                $sq->where('titulo', 'like', "%{$query}%")
                    ->orWhere('medio', 'like', "%{$query}%");
            });
        }
        if ($request->boolean('soloDestacadas', false)) {
            $q->where('destacada', true);
        }
        if ($request->boolean('soloActivos', false)) {
            $q->where('activo', true);
        }

        $total = $q->count();
        $data = $q->orderByDesc('fecha_publicacion')->orderBy('orden')->offset(($page - 1) * $size)->limit($size)->get();

        return response()->json(['data' => $data, 'total' => $total]);
    }

    public function show(int $id): JsonResponse
    {
        $row = DB::table('web_nota_prensa')->find($id);
        if (! $row) {
            abort(404, 'Nota de prensa no encontrada');
        }

        return response()->json($row);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'titulo' => ['required', 'string', 'max:300'],
            'medio' => ['required', 'string', 'max:200'],
            'logo_medio_url' => ['nullable', 'string', 'max:255'],
            'logo_medio_alt' => ['nullable', 'string', 'max:255'],
            'resumen' => ['nullable', 'string'],
            'url_noticia' => ['nullable', 'string', 'max:500'],
            'fecha_publicacion' => ['required', 'date'],
            'destacada' => ['nullable', 'boolean'],
            'orden' => ['nullable', 'integer'],
            'activo' => ['nullable', 'boolean'],
        ]);
        $data['destacada'] = $request->boolean('destacada', false);
        $data['orden'] = $request->integer('orden', 0);
        $data['activo'] = $request->boolean('activo', true);
        $data['created_at'] = now();

        $id = DB::table('web_nota_prensa')->insertGetId($data);
        $row = DB::table('web_nota_prensa')->find($id);

        return response()->json($row, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = DB::table('web_nota_prensa')->find($id);
        if (! $row) {
            abort(404, 'Nota de prensa no encontrada');
        }

        $data = $request->validate([
            'titulo' => ['sometimes', 'required', 'string', 'max:300'],
            'medio' => ['sometimes', 'required', 'string', 'max:200'],
            'logo_medio_url' => ['nullable', 'string', 'max:255'],
            'logo_medio_alt' => ['nullable', 'string', 'max:255'],
            'resumen' => ['nullable', 'string'],
            'url_noticia' => ['nullable', 'string', 'max:500'],
            'fecha_publicacion' => ['sometimes', 'required', 'date'],
            'destacada' => ['nullable', 'boolean'],
            'orden' => ['nullable', 'integer'],
            'activo' => ['nullable', 'boolean'],
        ]);
        $data['updated_at'] = now();
        DB::table('web_nota_prensa')->where('id', $id)->update($data);

        return response()->json(DB::table('web_nota_prensa')->find($id));
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = DB::table('web_nota_prensa')->where('id', $id)->delete();
        if (! $deleted) {
            abort(404, 'Nota de prensa no encontrada');
        }

        return response()->json(null, 204);
    }
}
