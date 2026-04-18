<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MateriaPlanController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $size = (int) $request->get('pageSize', 100);
        $page = (int) $request->get('pageIndex', 1);

        $q = DB::table('t_materia_plan');
        if ($request->has('id_plan')) {
            $q->where('id_plan', (int) $request->get('id_plan'));
        }
        if ($request->has('id_mat')) {
            $q->where('id_mat', (int) $request->get('id_mat'));
        }
        if (! $request->boolean('conInactivos', false)) {
            $q->where('estado', 1);
        }

        $total = $q->count();
        $data = $q->orderBy('id_matplan')->offset(($page - 1) * $size)->limit($size)->get();

        return response()->json(['data' => $data, 'total' => $total]);
    }

    public function show(int $id): JsonResponse
    {
        $row = DB::table('t_materia_plan')->where('id_matplan', $id)->first();
        if (! $row) {
            abort(404, 'Materia-Plan no encontrada');
        }

        return response()->json($row);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_matplan' => ['required', 'integer'],
            'id_us_reg' => ['nullable', 'integer'],
            'num_mp' => ['nullable', 'integer'],
            'id_mat' => ['required', 'integer'],
            'id_plan' => ['required', 'integer'],
            'carga_horaria_plan' => ['nullable', 'string', 'max:200'],
            'id_preesp' => ['nullable', 'integer'],
            'estado' => ['nullable', 'integer'],
        ]);
        $data['id_us_reg'] = $request->integer('id_us_reg', 0);
        $data['num_mp'] = $request->integer('num_mp', 0);
        $data['estado'] = $request->integer('estado', 1);
        $data['fecha_reg'] = now();

        DB::table('t_materia_plan')->insert($data);

        return response()->json(DB::table('t_materia_plan')->where('id_matplan', $data['id_matplan'])->first(), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = DB::table('t_materia_plan')->where('id_matplan', $id)->first();
        if (! $row) {
            abort(404, 'Materia-Plan no encontrada');
        }

        $data = $request->validate([
            'id_mat' => ['sometimes', 'required', 'integer'],
            'id_plan' => ['sometimes', 'required', 'integer'],
            'carga_horaria_plan' => ['nullable', 'string', 'max:200'],
            'id_preesp' => ['nullable', 'integer'],
            'estado' => ['nullable', 'integer'],
        ]);
        DB::table('t_materia_plan')->where('id_matplan', $id)->update($data);

        return response()->json(DB::table('t_materia_plan')->where('id_matplan', $id)->first());
    }

    public function destroy(int $id): JsonResponse
    {
        DB::table('t_materia_plan')->where('id_matplan', $id)->update(['estado' => 0]);

        return response()->json(null, 204);
    }
}
