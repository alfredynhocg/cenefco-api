<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $size = (int) $request->get('pageSize', 30);
        $page = (int) $request->get('pageIndex', 1);

        $q = DB::table('t_carta');
        if ($request->has('id_us')) {
            $q->where('id_us', (int) $request->get('id_us'));
        }
        if ($request->has('id_plan')) {
            $q->where('id_plan', (int) $request->get('id_plan'));
        }
        if (! $request->boolean('conInactivos', false)) {
            $q->where('estado', 1);
        }

        $total = $q->count();
        $data = $q->orderByDesc('id_carta')->offset(($page - 1) * $size)->limit($size)->get();

        return response()->json(['data' => $data, 'total' => $total]);
    }

    public function show(int $id): JsonResponse
    {
        $row = DB::table('t_carta')->where('id_carta', $id)->first();
        if (! $row) {
            abort(404, 'Carta no encontrada');
        }

        return response()->json($row);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_carta' => ['required', 'integer'],
            'id_us_reg' => ['nullable', 'integer'],
            'num_carta' => ['nullable', 'integer'],
            'id_us' => ['nullable', 'integer'],
            'id_plan' => ['nullable', 'integer'],
            'nombresenor' => ['nullable', 'string', 'max:200'],
            'nombretitulo' => ['nullable', 'string', 'max:200'],
            'textocarta1' => ['nullable', 'string'],
            'textocarta2' => ['nullable', 'string'],
            'textocarta3' => ['nullable', 'string'],
            'estado' => ['nullable', 'integer'],
        ]);
        $data['id_us_reg'] = $request->integer('id_us_reg', 0);
        $data['num_carta'] = $request->integer('num_carta', 0);
        $data['estado'] = $request->integer('estado', 1);
        $data['fecha_reg'] = now();

        DB::table('t_carta')->insert($data);

        return response()->json(DB::table('t_carta')->where('id_carta', $data['id_carta'])->first(), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = DB::table('t_carta')->where('id_carta', $id)->first();
        if (! $row) {
            abort(404, 'Carta no encontrada');
        }

        $data = $request->validate([
            'nombresenor' => ['nullable', 'string', 'max:200'],
            'nombretitulo' => ['nullable', 'string', 'max:200'],
            'textocarta1' => ['nullable', 'string'],
            'textocarta2' => ['nullable', 'string'],
            'textocarta3' => ['nullable', 'string'],
            'estado' => ['nullable', 'integer'],
        ]);
        DB::table('t_carta')->where('id_carta', $id)->update($data);

        return response()->json(DB::table('t_carta')->where('id_carta', $id)->first());
    }

    public function destroy(int $id): JsonResponse
    {
        DB::table('t_carta')->where('id_carta', $id)->update(['estado' => 0]);

        return response()->json(null, 204);
    }
}
