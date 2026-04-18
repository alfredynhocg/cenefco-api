<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MoodleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = $request->get('query', '');
        $size = (int) $request->get('pageSize', 30);
        $page = (int) $request->get('pageIndex', 1);

        $q = DB::table('t_moodle');
        if ($query) {
            $q->where('titulo_moodle', 'like', "%{$query}%");
        }
        if (! $request->boolean('conInactivos', false)) {
            $q->where('estado', 1);
        }

        $total = $q->count();
        $data = $q->orderBy('titulo_moodle')->offset(($page - 1) * $size)->limit($size)->get();

        return response()->json(['data' => $data, 'total' => $total]);
    }

    public function show(int $id): JsonResponse
    {
        $row = DB::table('t_moodle')->where('id_moodle', $id)->first();
        if (! $row) {
            abort(404, 'Moodle no encontrado');
        }

        return response()->json($row);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_moodle' => ['required', 'integer'],
            'id_us_reg' => ['nullable', 'integer'],
            'num_moodle' => ['nullable', 'integer'],
            'titulo_moodle' => ['required', 'string', 'max:200'],
            'cp_moodle_servidor' => ['nullable', 'string', 'max:200'],
            'cp_moodle_base_datos' => ['nullable', 'string', 'max:200'],
            'cp_moodle_usuario_bd' => ['nullable', 'string', 'max:200'],
            'cp_moodle_contrasena' => ['nullable', 'string', 'max:200'],
            'cp_url_campus' => ['nullable', 'string', 'max:200'],
            'estado' => ['nullable', 'integer'],
        ]);
        $data['id_us_reg'] = $request->integer('id_us_reg', 0);
        $data['num_moodle'] = $request->integer('num_moodle', 0);
        $data['estado'] = $request->integer('estado', 1);
        $data['fecha_reg'] = now();

        DB::table('t_moodle')->insert($data);

        return response()->json(DB::table('t_moodle')->where('id_moodle', $data['id_moodle'])->first(), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = DB::table('t_moodle')->where('id_moodle', $id)->first();
        if (! $row) {
            abort(404, 'Moodle no encontrado');
        }

        $data = $request->validate([
            'titulo_moodle' => ['sometimes', 'required', 'string', 'max:200'],
            'cp_moodle_servidor' => ['nullable', 'string', 'max:200'],
            'cp_moodle_base_datos' => ['nullable', 'string', 'max:200'],
            'cp_moodle_usuario_bd' => ['nullable', 'string', 'max:200'],
            'cp_moodle_contrasena' => ['nullable', 'string', 'max:200'],
            'cp_url_campus' => ['nullable', 'string', 'max:200'],
            'estado' => ['nullable', 'integer'],
        ]);
        DB::table('t_moodle')->where('id_moodle', $id)->update($data);

        return response()->json(DB::table('t_moodle')->where('id_moodle', $id)->first());
    }

    public function destroy(int $id): JsonResponse
    {
        DB::table('t_moodle')->where('id_moodle', $id)->update(['estado' => 0]);

        return response()->json(null, 204);
    }
}
