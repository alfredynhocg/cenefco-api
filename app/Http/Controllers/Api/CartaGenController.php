<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartaGenController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $size = (int) $request->get('pageSize', 30);
        $page = (int) $request->get('pageIndex', 1);

        $q = DB::table('t_cartagen');
        if ($request->has('id_us')) {
            $q->where('id_us', (int) $request->get('id_us'));
        }
        if ($request->has('id_cartamod')) {
            $q->where('id_cartamod', (int) $request->get('id_cartamod'));
        }
        if (! $request->boolean('conInactivos', false)) {
            $q->where('estado', 1);
        }

        $total = $q->count();
        $data = $q->orderByDesc('id_cartagen')->offset(($page - 1) * $size)->limit($size)->get();

        return response()->json(['data' => $data, 'total' => $total]);
    }

    public function show(int $id): JsonResponse
    {
        $row = DB::table('t_cartagen')->where('id_cartagen', $id)->first();
        if (! $row) {
            abort(404, 'Carta generada no encontrada');
        }

        return response()->json($row);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_cartagen' => ['required', 'integer'],
            'id_us_reg' => ['nullable', 'integer'],
            'num_carta' => ['nullable', 'integer'],
            'id_us' => ['nullable', 'integer'],
            'id_cartamod' => ['nullable', 'integer'],
            'textocarta' => ['nullable', 'string'],
            'textocarta1' => ['nullable', 'string'],
            'textocarta3' => ['nullable', 'string'],
            'usar_encabezado_pie_estandar' => ['nullable', 'integer'],
            'cp_nro_contrato' => ['nullable', 'integer'],
            'cp_gestion_contrato' => ['nullable', 'string', 'max:200'],
            'estado' => ['nullable', 'integer'],
        ]);
        $data['id_us_reg'] = $request->integer('id_us_reg', 0);
        $data['num_carta'] = $request->integer('num_carta', 0);
        $data['estado'] = $request->integer('estado', 1);
        $data['fecha_reg'] = now();

        DB::table('t_cartagen')->insert($data);

        return response()->json(DB::table('t_cartagen')->where('id_cartagen', $data['id_cartagen'])->first(), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = DB::table('t_cartagen')->where('id_cartagen', $id)->first();
        if (! $row) {
            abort(404, 'Carta generada no encontrada');
        }

        $data = $request->validate([
            'textocarta' => ['nullable', 'string'],
            'textocarta1' => ['nullable', 'string'],
            'textocarta3' => ['nullable', 'string'],
            'usar_encabezado_pie_estandar' => ['nullable', 'integer'],
            'cp_nro_contrato' => ['nullable', 'integer'],
            'cp_gestion_contrato' => ['nullable', 'string', 'max:200'],
            'estado' => ['nullable', 'integer'],
        ]);
        DB::table('t_cartagen')->where('id_cartagen', $id)->update($data);

        return response()->json(DB::table('t_cartagen')->where('id_cartagen', $id)->first());
    }

    public function destroy(int $id): JsonResponse
    {
        DB::table('t_cartagen')->where('id_cartagen', $id)->update(['estado' => 0]);

        return response()->json(null, 204);
    }
}
