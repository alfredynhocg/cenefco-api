<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UsuarioMoodleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $size = (int) $request->get('pageSize', 50);
        $page = (int) $request->get('pageIndex', 1);

        $q = DB::table('t_usuariomoodle');
        if ($request->has('id_us')) {
            $q->where('id_us', (int) $request->get('id_us'));
        }
        if ($request->has('id_moodle')) {
            $q->where('id_moodle', (int) $request->get('id_moodle'));
        }
        if (! $request->boolean('conInactivos', false)) {
            $q->where('estado', 1);
        }

        $total = $q->count();
        $data = $q->orderBy('id_usmoodle')->offset(($page - 1) * $size)->limit($size)->get();

        return response()->json(['data' => $data, 'total' => $total]);
    }

    public function show(int $id): JsonResponse
    {
        $row = DB::table('t_usuariomoodle')->where('id_usmoodle', $id)->first();
        if (! $row) {
            abort(404, 'Usuario Moodle no encontrado');
        }

        return response()->json($row);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_usmoodle' => ['required', 'integer'],
            'id_us_reg' => ['nullable', 'integer'],
            'num_usmoodle' => ['nullable', 'integer'],
            'id_us' => ['required', 'integer'],
            'id_moodle' => ['required', 'integer'],
            'moodle_id_user' => ['nullable', 'string', 'max:200'],
            'estado' => ['nullable', 'integer'],
        ]);
        $data['id_us_reg'] = $request->integer('id_us_reg', 0);
        $data['num_usmoodle'] = $request->integer('num_usmoodle', 0);
        $data['estado'] = $request->integer('estado', 1);
        $data['fecha_reg'] = now();

        DB::table('t_usuariomoodle')->insert($data);

        return response()->json(DB::table('t_usuariomoodle')->where('id_usmoodle', $data['id_usmoodle'])->first(), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = DB::table('t_usuariomoodle')->where('id_usmoodle', $id)->first();
        if (! $row) {
            abort(404, 'Usuario Moodle no encontrado');
        }

        $data = $request->validate([
            'id_moodle' => ['nullable', 'integer'],
            'moodle_id_user' => ['nullable', 'string', 'max:200'],
            'estado' => ['nullable', 'integer'],
        ]);
        DB::table('t_usuariomoodle')->where('id_usmoodle', $id)->update($data);

        return response()->json(DB::table('t_usuariomoodle')->where('id_usmoodle', $id)->first());
    }

    public function destroy(int $id): JsonResponse
    {
        DB::table('t_usuariomoodle')->where('id_usmoodle', $id)->update(['estado' => 0]);

        return response()->json(null, 204);
    }
}
