<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MateriaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = $request->get('query', '');
        $size = (int) $request->get('pageSize', 50);
        $page = (int) $request->get('pageIndex', 1);

        $q = DB::table('t_materia');
        if ($query) {
            $q->where(function ($sq) use ($query) {
                $sq->where('nombre', 'like', "%{$query}%")
                    ->orWhere('sigla', 'like', "%{$query}%");
            });
        }
        if (! $request->boolean('conInactivos', false)) {
            $q->where('estado', 1);
        }

        $total = $q->count();
        $data = $q->orderBy('nombre')->offset(($page - 1) * $size)->limit($size)->get();

        return response()->json(['data' => $data, 'total' => $total]);
    }

    public function show(int $id): JsonResponse
    {
        $row = DB::table('t_materia')->where('id_mat', $id)->first();
        if (! $row) {
            abort(404, 'Materia no encontrada');
        }

        return response()->json($row);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_mat' => ['required', 'integer'],
            'id_us_reg' => ['nullable', 'integer'],
            'sigla' => ['nullable', 'string', 'max:200'],
            'nombremat' => ['nullable', 'string', 'max:200'],
            'nombre' => ['required', 'string', 'max:200'],
            'semestre' => ['nullable', 'string', 'max:200'],
            'modalidad' => ['nullable', 'integer'],
            'carga_horaria' => ['nullable', 'string', 'max:200'],
            'observacion' => ['nullable', 'string'],
            'estado' => ['nullable', 'integer'],
        ]);
        $data['id_us_reg'] = $request->integer('id_us_reg', 0);
        $data['estado'] = $request->integer('estado', 1);
        $data['fecha_reg'] = now();

        DB::table('t_materia')->insert($data);

        return response()->json(DB::table('t_materia')->where('id_mat', $data['id_mat'])->first(), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = DB::table('t_materia')->where('id_mat', $id)->first();
        if (! $row) {
            abort(404, 'Materia no encontrada');
        }

        $data = $request->validate([
            'sigla' => ['nullable', 'string', 'max:200'],
            'nombremat' => ['nullable', 'string', 'max:200'],
            'nombre' => ['sometimes', 'required', 'string', 'max:200'],
            'semestre' => ['nullable', 'string', 'max:200'],
            'modalidad' => ['nullable', 'integer'],
            'carga_horaria' => ['nullable', 'string', 'max:200'],
            'observacion' => ['nullable', 'string'],
            'estado' => ['nullable', 'integer'],
        ]);
        DB::table('t_materia')->where('id_mat', $id)->update($data);

        return response()->json(DB::table('t_materia')->where('id_mat', $id)->first());
    }

    public function destroy(int $id): JsonResponse
    {
        DB::table('t_materia')->where('id_mat', $id)->update(['estado' => 0]);

        return response()->json(null, 204);
    }
}
