<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartaModeloController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = $request->get('query', '');
        $size = (int) $request->get('pageSize', 50);
        $page = (int) $request->get('pageIndex', 1);

        $q = DB::table('t_cartamodelo');
        if ($query) {
            $q->where('nombremodelo', 'like', "%{$query}%");
        }
        if (! $request->boolean('conInactivos', false)) {
            $q->where('estado', 1);
        }

        $total = $q->count();
        $data = $q->orderBy('nombremodelo')->offset(($page - 1) * $size)->limit($size)->get();

        return response()->json(['data' => $data, 'total' => $total]);
    }

    public function show(int $id): JsonResponse
    {
        $row = DB::table('t_cartamodelo')->where('id_cartamod', $id)->first();
        if (! $row) {
            abort(404, 'Modelo de carta no encontrado');
        }

        return response()->json($row);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_cartamod' => ['required', 'integer'],
            'id_us_reg' => ['nullable', 'integer'],
            'num_cartamod' => ['nullable', 'integer'],
            'nombremodelo' => ['required', 'string', 'max:200'],
            'textocarta' => ['nullable', 'string'],
            'textocarta1' => ['nullable', 'string'],
            'textocarta3' => ['nullable', 'string'],
            'texto_carta' => ['nullable', 'string'],
            'usar_encabezado_pie_estandar' => ['nullable', 'integer'],
            'estado' => ['nullable', 'integer'],
        ]);
        $data['id_us_reg'] = $request->integer('id_us_reg', 0);
        $data['num_cartamod'] = $request->integer('num_cartamod', 0);
        $data['estado'] = $request->integer('estado', 1);
        $data['fecha_reg'] = now();

        DB::table('t_cartamodelo')->insert($data);

        return response()->json(DB::table('t_cartamodelo')->where('id_cartamod', $data['id_cartamod'])->first(), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = DB::table('t_cartamodelo')->where('id_cartamod', $id)->first();
        if (! $row) {
            abort(404, 'Modelo de carta no encontrado');
        }

        $data = $request->validate([
            'nombremodelo' => ['sometimes', 'required', 'string', 'max:200'],
            'textocarta' => ['nullable', 'string'],
            'textocarta1' => ['nullable', 'string'],
            'textocarta3' => ['nullable', 'string'],
            'texto_carta' => ['nullable', 'string'],
            'usar_encabezado_pie_estandar' => ['nullable', 'integer'],
            'estado' => ['nullable', 'integer'],
        ]);
        DB::table('t_cartamodelo')->where('id_cartamod', $id)->update($data);

        return response()->json(DB::table('t_cartamodelo')->where('id_cartamod', $id)->first());
    }

    public function destroy(int $id): JsonResponse
    {
        DB::table('t_cartamodelo')->where('id_cartamod', $id)->update(['estado' => 0]);

        return response()->json(null, 204);
    }
}
