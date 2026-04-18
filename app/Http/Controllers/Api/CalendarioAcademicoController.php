<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CalendarioAcademicoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = $request->get('query', '');
        $size = (int) $request->get('pageSize', 50);
        $page = (int) $request->get('pageIndex', 1);

        $q = DB::table('web_calendario_academico');
        if ($query) {
            $q->where('titulo', 'like', "%{$query}%");
        }
        if ($request->has('tipo')) {
            $q->where('tipo', $request->get('tipo'));
        }
        if ($request->has('programa_id')) {
            $q->where('programa_id', (int) $request->get('programa_id'));
        }
        if ($request->boolean('soloPublicos', false)) {
            $q->where('publico', true);
        }

        $total = $q->count();
        $data = $q->orderBy('fecha_inicio')->offset(($page - 1) * $size)->limit($size)->get();

        return response()->json(['data' => $data, 'total' => $total]);
    }

    public function show(int $id): JsonResponse
    {
        $row = DB::table('web_calendario_academico')->find($id);
        if (! $row) {
            abort(404, 'Evento de calendario no encontrado');
        }

        return response()->json($row);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'titulo' => ['required', 'string', 'max:300'],
            'descripcion' => ['nullable', 'string'],
            'tipo' => ['nullable', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'max:7'],
            'programa_id' => ['nullable', 'integer'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['nullable', 'date'],
            'todo_el_dia' => ['nullable', 'boolean'],
            'destacado' => ['nullable', 'boolean'],
            'publico' => ['nullable', 'boolean'],
        ]);
        $data['todo_el_dia'] = $request->boolean('todo_el_dia', true);
        $data['destacado'] = $request->boolean('destacado', false);
        $data['publico'] = $request->boolean('publico', true);
        $data['created_at'] = now();

        $id = DB::table('web_calendario_academico')->insertGetId($data);
        $row = DB::table('web_calendario_academico')->find($id);

        return response()->json($row, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = DB::table('web_calendario_academico')->find($id);
        if (! $row) {
            abort(404, 'Evento de calendario no encontrado');
        }

        $data = $request->validate([
            'titulo' => ['sometimes', 'required', 'string', 'max:300'],
            'descripcion' => ['nullable', 'string'],
            'tipo' => ['nullable', 'string', 'max:100'],
            'color' => ['nullable', 'string', 'max:7'],
            'programa_id' => ['nullable', 'integer'],
            'fecha_inicio' => ['sometimes', 'required', 'date'],
            'fecha_fin' => ['nullable', 'date'],
            'todo_el_dia' => ['nullable', 'boolean'],
            'destacado' => ['nullable', 'boolean'],
            'publico' => ['nullable', 'boolean'],
        ]);
        $data['updated_at'] = now();
        DB::table('web_calendario_academico')->where('id', $id)->update($data);

        return response()->json(DB::table('web_calendario_academico')->find($id));
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = DB::table('web_calendario_academico')->where('id', $id)->delete();
        if (! $deleted) {
            abort(404, 'Evento de calendario no encontrado');
        }

        return response()->json(null, 204);
    }
}
