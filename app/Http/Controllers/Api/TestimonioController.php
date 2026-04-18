<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TestimonioController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = $request->get('query', '');
        $size = (int) $request->get('pageSize', 20);
        $page = (int) $request->get('pageIndex', 1);

        $q = DB::table('web_testimonio');
        if ($query) {
            $q->where(function ($sq) use ($query) {
                $sq->where('nombre', 'like', "%{$query}%")
                    ->orWhere('testimonio', 'like', "%{$query}%");
            });
        }
        if ($request->has('estado')) {
            $q->where('estado', $request->get('estado'));
        }
        if ($request->boolean('soloDestacados', false)) {
            $q->where('destacado', true);
        }

        $total = $q->count();
        $data = $q->orderBy('orden')->orderByDesc('id')->offset(($page - 1) * $size)->limit($size)->get();

        return response()->json(['data' => $data, 'total' => $total]);
    }

    public function show(int $id): JsonResponse
    {
        $row = DB::table('web_testimonio')->find($id);
        if (! $row) {
            abort(404, 'Testimonio no encontrado');
        }

        return response()->json($row);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:200'],
            'cargo' => ['nullable', 'string', 'max:200'],
            'empresa' => ['nullable', 'string', 'max:200'],
            'testimonio' => ['required', 'string'],
            'calificacion' => ['nullable', 'integer', 'min:1', 'max:5'],
            'foto_url' => ['nullable', 'string', 'max:255'],
            'foto_alt' => ['nullable', 'string', 'max:255'],
            'programa_id' => ['nullable', 'integer'],
            'destacado' => ['nullable', 'boolean'],
            'orden' => ['nullable', 'integer'],
            'estado' => ['nullable', 'string', 'max:50'],
        ]);
        $data['calificacion'] = $request->integer('calificacion', 5);
        $data['destacado'] = $request->boolean('destacado', false);
        $data['orden'] = $request->integer('orden', 0);
        $data['estado'] = $data['estado'] ?? 'publicado';
        $data['created_at'] = now();

        $id = DB::table('web_testimonio')->insertGetId($data);
        $row = DB::table('web_testimonio')->find($id);

        return response()->json($row, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = DB::table('web_testimonio')->find($id);
        if (! $row) {
            abort(404, 'Testimonio no encontrado');
        }

        $data = $request->validate([
            'nombre' => ['sometimes', 'required', 'string', 'max:200'],
            'cargo' => ['nullable', 'string', 'max:200'],
            'empresa' => ['nullable', 'string', 'max:200'],
            'testimonio' => ['sometimes', 'required', 'string'],
            'calificacion' => ['nullable', 'integer', 'min:1', 'max:5'],
            'foto_url' => ['nullable', 'string', 'max:255'],
            'foto_alt' => ['nullable', 'string', 'max:255'],
            'programa_id' => ['nullable', 'integer'],
            'destacado' => ['nullable', 'boolean'],
            'orden' => ['nullable', 'integer'],
            'estado' => ['nullable', 'string', 'max:50'],
        ]);
        $data['updated_at'] = now();
        DB::table('web_testimonio')->where('id', $id)->update($data);

        return response()->json(DB::table('web_testimonio')->find($id));
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = DB::table('web_testimonio')->where('id', $id)->delete();
        if (! $deleted) {
            abort(404, 'Testimonio no encontrado');
        }

        return response()->json(null, 204);
    }
}
