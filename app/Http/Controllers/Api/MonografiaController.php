<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MonografiaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = $request->get('query', '');
        $size = (int) $request->get('pageSize', 30);
        $page = (int) $request->get('pageIndex', 1);

        $q = DB::table('t_monografia');
        if ($query) {
            $q->where(function ($sq) use ($query) {
                $sq->where('titulo_monografia', 'like', "%{$query}%")
                    ->orWhere('autor', 'like', "%{$query}%");
            });
        }
        if (! $request->boolean('conInactivos', false)) {
            $q->where('estado', 1);
        }

        $total = $q->count();
        $data = $q->orderByDesc('fecha_publicacion')->offset(($page - 1) * $size)->limit($size)->get();

        return response()->json(['data' => $data, 'total' => $total]);
    }

    public function show(int $id): JsonResponse
    {
        $row = DB::table('t_monografia')->where('id_monografia', $id)->first();
        if (! $row) {
            abort(404, 'Monografía no encontrada');
        }

        return response()->json($row);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_monografia' => ['required', 'integer'],
            'id_us_reg' => ['nullable', 'integer'],
            'num_monografia' => ['nullable', 'integer'],
            'titulo_monografia' => ['required', 'string', 'max:200'],
            'descripcion_monografia' => ['nullable', 'string'],
            'fecha_publicacion' => ['nullable', 'date'],
            'autor' => ['nullable', 'string', 'max:200'],
            'archivo' => ['nullable', 'string', 'max:200'],
            'estado' => ['nullable', 'integer'],
        ]);
        $data['id_us_reg'] = $request->integer('id_us_reg', 0);
        $data['num_monografia'] = $request->integer('num_monografia', 0);
        $data['estado'] = $request->integer('estado', 1);
        $data['fecha_reg'] = now();

        DB::table('t_monografia')->insert($data);

        return response()->json(DB::table('t_monografia')->where('id_monografia', $data['id_monografia'])->first(), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = DB::table('t_monografia')->where('id_monografia', $id)->first();
        if (! $row) {
            abort(404, 'Monografía no encontrada');
        }

        $data = $request->validate([
            'titulo_monografia' => ['sometimes', 'required', 'string', 'max:200'],
            'descripcion_monografia' => ['nullable', 'string'],
            'fecha_publicacion' => ['nullable', 'date'],
            'autor' => ['nullable', 'string', 'max:200'],
            'archivo' => ['nullable', 'string', 'max:200'],
            'estado' => ['nullable', 'integer'],
        ]);
        DB::table('t_monografia')->where('id_monografia', $id)->update($data);

        return response()->json(DB::table('t_monografia')->where('id_monografia', $id)->first());
    }

    public function destroy(int $id): JsonResponse
    {
        DB::table('t_monografia')->where('id_monografia', $id)->update(['estado' => 0]);

        return response()->json(null, 204);
    }
}
