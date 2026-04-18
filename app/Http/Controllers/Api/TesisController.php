<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TesisController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = $request->get('query', '');
        $size = (int) $request->get('pageSize', 30);
        $page = (int) $request->get('pageIndex', 1);

        $q = DB::table('t_tesis');
        if ($query) {
            $q->where(function ($sq) use ($query) {
                $sq->where('titulo_tesis', 'like', "%{$query}%")
                    ->orWhere('autor', 'like', "%{$query}%");
            });
        }
        if ($request->has('tipo_tesis')) {
            $q->where('tipo_tesis', (int) $request->get('tipo_tesis'));
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
        $row = DB::table('t_tesis')->where('id_tesis', $id)->first();
        if (! $row) {
            abort(404, 'Tesis no encontrada');
        }

        return response()->json($row);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_tesis' => ['required', 'integer'],
            'id_us_reg' => ['nullable', 'integer'],
            'num_tesis' => ['nullable', 'integer'],
            'titulo_tesis' => ['required', 'string', 'max:200'],
            'descripcion_tesis' => ['nullable', 'string'],
            'fecha_publicacion' => ['nullable', 'date'],
            'autor' => ['nullable', 'string', 'max:200'],
            'tipo_tesis' => ['nullable', 'integer'],
            'archivo' => ['nullable', 'string', 'max:200'],
            'estado' => ['nullable', 'integer'],
        ]);
        $data['id_us_reg'] = $request->integer('id_us_reg', 0);
        $data['num_tesis'] = $request->integer('num_tesis', 0);
        $data['estado'] = $request->integer('estado', 1);
        $data['fecha_reg'] = now();

        DB::table('t_tesis')->insert($data);

        return response()->json(DB::table('t_tesis')->where('id_tesis', $data['id_tesis'])->first(), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = DB::table('t_tesis')->where('id_tesis', $id)->first();
        if (! $row) {
            abort(404, 'Tesis no encontrada');
        }

        $data = $request->validate([
            'titulo_tesis' => ['sometimes', 'required', 'string', 'max:200'],
            'descripcion_tesis' => ['nullable', 'string'],
            'fecha_publicacion' => ['nullable', 'date'],
            'autor' => ['nullable', 'string', 'max:200'],
            'tipo_tesis' => ['nullable', 'integer'],
            'archivo' => ['nullable', 'string', 'max:200'],
            'estado' => ['nullable', 'integer'],
        ]);
        DB::table('t_tesis')->where('id_tesis', $id)->update($data);

        return response()->json(DB::table('t_tesis')->where('id_tesis', $id)->first());
    }

    public function destroy(int $id): JsonResponse
    {
        DB::table('t_tesis')->where('id_tesis', $id)->update(['estado' => 0]);

        return response()->json(null, 204);
    }
}
