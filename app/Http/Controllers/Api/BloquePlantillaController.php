<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BloquePlantillaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = $request->get('query', '');
        $size = (int) $request->get('pageSize', 50);
        $page = (int) $request->get('pageIndex', 1);

        $q = DB::table('t_bloqueplantilla');
        if ($query) {
            $q->where('titulo_bloqueplantilla', 'like', "%{$query}%");
        }
        if (! $request->boolean('conInactivos', false)) {
            $q->where('estado', 1);
        }

        $total = $q->count();
        $data = $q->orderBy('titulo_bloqueplantilla')->offset(($page - 1) * $size)->limit($size)->get();

        return response()->json(['data' => $data, 'total' => $total]);
    }

    public function show(int $id): JsonResponse
    {
        $row = DB::table('t_bloqueplantilla')->where('id_bloqueplantilla', $id)->first();
        if (! $row) {
            abort(404, 'Bloque plantilla no encontrado');
        }

        return response()->json($row);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_bloqueplantilla' => ['required', 'integer'],
            'id_us_reg' => ['nullable', 'integer'],
            'num_bloqueplantilla' => ['nullable', 'integer'],
            'titulo_bloqueplantilla' => ['required', 'string', 'max:200'],
            'cod_seccion' => ['nullable', 'string'],
            'codigo_html' => ['nullable', 'string'],
            'numero_bloques' => ['nullable', 'string', 'max:200'],
            'estado' => ['nullable', 'integer'],
        ]);
        $data['id_us_reg'] = $request->integer('id_us_reg', 0);
        $data['num_bloqueplantilla'] = $request->integer('num_bloqueplantilla', 0);
        $data['estado'] = $request->integer('estado', 1);
        $data['fecha_reg'] = now();

        DB::table('t_bloqueplantilla')->insert($data);

        return response()->json(DB::table('t_bloqueplantilla')->where('id_bloqueplantilla', $data['id_bloqueplantilla'])->first(), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = DB::table('t_bloqueplantilla')->where('id_bloqueplantilla', $id)->first();
        if (! $row) {
            abort(404, 'Bloque plantilla no encontrado');
        }

        $data = $request->validate([
            'titulo_bloqueplantilla' => ['sometimes', 'required', 'string', 'max:200'],
            'cod_seccion' => ['nullable', 'string'],
            'codigo_html' => ['nullable', 'string'],
            'numero_bloques' => ['nullable', 'string', 'max:200'],
            'estado' => ['nullable', 'integer'],
        ]);
        DB::table('t_bloqueplantilla')->where('id_bloqueplantilla', $id)->update($data);

        return response()->json(DB::table('t_bloqueplantilla')->where('id_bloqueplantilla', $id)->first());
    }

    public function destroy(int $id): JsonResponse
    {
        DB::table('t_bloqueplantilla')->where('id_bloqueplantilla', $id)->update(['estado' => 0]);

        return response()->json(null, 204);
    }
}
