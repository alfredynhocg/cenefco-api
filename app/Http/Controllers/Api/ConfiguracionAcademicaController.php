<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConfiguracionAcademicaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $size = (int) $request->get('pageSize', 30);
        $page = (int) $request->get('pageIndex', 1);

        $q = DB::table('t_configuracion');
        if ($request->has('gestion')) {
            $q->where('gestion', $request->get('gestion'));
        }
        if ($request->has('id_plan')) {
            $q->where('id_plan', (int) $request->get('id_plan'));
        }
        if (! $request->boolean('conInactivos', false)) {
            $q->where('estado', 1);
        }

        $total = $q->count();
        $data = $q->orderByDesc('gestion')->offset(($page - 1) * $size)->limit($size)->get();

        return response()->json(['data' => $data, 'total' => $total]);
    }

    public function show(int $id): JsonResponse
    {
        $row = DB::table('t_configuracion')->where('id_conf', $id)->first();
        if (! $row) {
            abort(404, 'Configuración no encontrada');
        }

        return response()->json($row);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_conf' => ['required', 'integer'],
            'id_us_reg' => ['nullable', 'integer'],
            'num_conf' => ['nullable', 'integer'],
            'gestion' => ['nullable', 'string', 'max:10'],
            'periodo_est' => ['nullable', 'string', 'max:200'],
            'gestion_est' => ['nullable', 'string', 'max:200'],
            'max_materias_cursar' => ['nullable', 'string', 'max:200'],
            'id_plan' => ['nullable', 'integer'],
            'id_plan_anterior' => ['nullable', 'integer'],
            'periodo_doc' => ['nullable', 'string', 'max:200'],
            'gestion_doc' => ['nullable', 'string', 'max:200'],
            'correlativo' => ['nullable', 'string', 'max:200'],
            'nombre_kardista' => ['nullable', 'string', 'max:200'],
            'nombre_director' => ['nullable', 'string', 'max:200'],
            'titulo_carrera' => ['nullable', 'string', 'max:200'],
            'descripcion_resolucion' => ['nullable', 'string', 'max:200'],
            'cod_codigo' => ['nullable', 'string', 'max:200'],
            'lugar_x' => ['nullable', 'string', 'max:200'],
            'carrera' => ['nullable', 'string', 'max:200'],
            'area' => ['nullable', 'string', 'max:200'],
            'periodo' => ['nullable', 'string', 'max:30'],
            'estado' => ['nullable', 'integer'],
        ]);
        $data['id_us_reg'] = $request->integer('id_us_reg', 0);
        $data['num_conf'] = $request->integer('num_conf', 0);
        $data['estado'] = $request->integer('estado', 1);
        $data['fecha_reg'] = now();

        DB::table('t_configuracion')->insert($data);

        return response()->json(DB::table('t_configuracion')->where('id_conf', $data['id_conf'])->first(), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = DB::table('t_configuracion')->where('id_conf', $id)->first();
        if (! $row) {
            abort(404, 'Configuración no encontrada');
        }

        $data = $request->validate([
            'gestion' => ['nullable', 'string', 'max:10'],
            'periodo_est' => ['nullable', 'string', 'max:200'],
            'gestion_est' => ['nullable', 'string', 'max:200'],
            'max_materias_cursar' => ['nullable', 'string', 'max:200'],
            'id_plan' => ['nullable', 'integer'],
            'id_plan_anterior' => ['nullable', 'integer'],
            'periodo_doc' => ['nullable', 'string', 'max:200'],
            'gestion_doc' => ['nullable', 'string', 'max:200'],
            'correlativo' => ['nullable', 'string', 'max:200'],
            'nombre_kardista' => ['nullable', 'string', 'max:200'],
            'nombre_director' => ['nullable', 'string', 'max:200'],
            'titulo_carrera' => ['nullable', 'string', 'max:200'],
            'descripcion_resolucion' => ['nullable', 'string', 'max:200'],
            'lugar_x' => ['nullable', 'string', 'max:200'],
            'carrera' => ['nullable', 'string', 'max:200'],
            'area' => ['nullable', 'string', 'max:200'],
            'periodo' => ['nullable', 'string', 'max:30'],
            'estado' => ['nullable', 'integer'],
        ]);
        DB::table('t_configuracion')->where('id_conf', $id)->update($data);

        return response()->json(DB::table('t_configuracion')->where('id_conf', $id)->first());
    }

    public function destroy(int $id): JsonResponse
    {
        DB::table('t_configuracion')->where('id_conf', $id)->update(['estado' => 0]);

        return response()->json(null, 204);
    }
}
