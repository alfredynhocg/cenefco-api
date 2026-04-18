<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HorarioController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $size = (int) $request->get('pageSize', 50);
        $page = (int) $request->get('pageIndex', 1);

        $q = DB::table('t_horario');
        if ($request->has('id_imp')) {
            $q->where('id_imp', (int) $request->get('id_imp'));
        }
        if (! $request->boolean('conInactivos', false)) {
            $q->where('estado', 1);
        }

        $total = $q->count();
        $data = $q->orderBy('hora_inicio')->offset(($page - 1) * $size)->limit($size)->get();

        return response()->json(['data' => $data, 'total' => $total]);
    }

    public function show(int $id): JsonResponse
    {
        $row = DB::table('t_horario')->where('id_horar', $id)->first();
        if (! $row) {
            abort(404, 'Horario no encontrado');
        }

        return response()->json($row);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_horar' => ['required', 'integer'],
            'id_us_reg' => ['nullable', 'integer'],
            'id_imp' => ['required', 'integer'],
            'id_d' => ['nullable', 'integer'],
            'hora_inicio' => ['nullable', 'string', 'max:8'],
            'hora_fin' => ['nullable', 'string', 'max:8'],
            'periodos' => ['nullable', 'string', 'max:200'],
            'estado' => ['nullable', 'integer'],
        ]);
        $data['id_us_reg'] = $request->integer('id_us_reg', 0);
        $data['estado'] = $request->integer('estado', 1);
        $data['fecha_reg'] = now();

        DB::table('t_horario')->insert($data);

        return response()->json(DB::table('t_horario')->where('id_horar', $data['id_horar'])->first(), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = DB::table('t_horario')->where('id_horar', $id)->first();
        if (! $row) {
            abort(404, 'Horario no encontrado');
        }

        $data = $request->validate([
            'id_d' => ['nullable', 'integer'],
            'hora_inicio' => ['nullable', 'string', 'max:8'],
            'hora_fin' => ['nullable', 'string', 'max:8'],
            'periodos' => ['nullable', 'string', 'max:200'],
            'estado' => ['nullable', 'integer'],
        ]);
        DB::table('t_horario')->where('id_horar', $id)->update($data);

        return response()->json(DB::table('t_horario')->where('id_horar', $id)->first());
    }

    public function destroy(int $id): JsonResponse
    {
        DB::table('t_horario')->where('id_horar', $id)->update(['estado' => 0]);

        return response()->json(null, 204);
    }
}
