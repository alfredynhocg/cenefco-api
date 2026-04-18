<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RevistaCientificaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = $request->get('query', '');
        $size = (int) $request->get('pageSize', 30);
        $page = (int) $request->get('pageIndex', 1);

        $q = DB::table('t_revistacientifica');
        if ($query) {
            $q->where('titulo_revistacientifica', 'like', "%{$query}%");
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
        $row = DB::table('t_revistacientifica')->where('id_revistacientifica', $id)->first();
        if (! $row) {
            abort(404, 'Revista científica no encontrada');
        }

        return response()->json($row);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_revistacientifica' => ['required', 'integer'],
            'id_us_reg' => ['nullable', 'integer'],
            'num_revistacientifica' => ['nullable', 'integer'],
            'titulo_revistacientifica' => ['required', 'string', 'max:200'],
            'descripcion_revistacientifica' => ['nullable', 'string'],
            'fecha_publicacion' => ['nullable', 'date'],
            'archivo' => ['nullable', 'string', 'max:200'],
            'estado' => ['nullable', 'integer'],
        ]);
        $data['id_us_reg'] = $request->integer('id_us_reg', 0);
        $data['num_revistacientifica'] = $request->integer('num_revistacientifica', 0);
        $data['estado'] = $request->integer('estado', 1);
        $data['fecha_reg'] = now();

        DB::table('t_revistacientifica')->insert($data);

        return response()->json(DB::table('t_revistacientifica')->where('id_revistacientifica', $data['id_revistacientifica'])->first(), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = DB::table('t_revistacientifica')->where('id_revistacientifica', $id)->first();
        if (! $row) {
            abort(404, 'Revista científica no encontrada');
        }

        $data = $request->validate([
            'titulo_revistacientifica' => ['sometimes', 'required', 'string', 'max:200'],
            'descripcion_revistacientifica' => ['nullable', 'string'],
            'fecha_publicacion' => ['nullable', 'date'],
            'archivo' => ['nullable', 'string', 'max:200'],
            'estado' => ['nullable', 'integer'],
        ]);
        DB::table('t_revistacientifica')->where('id_revistacientifica', $id)->update($data);

        return response()->json(DB::table('t_revistacientifica')->where('id_revistacientifica', $id)->first());
    }

    public function destroy(int $id): JsonResponse
    {
        DB::table('t_revistacientifica')->where('id_revistacientifica', $id)->update(['estado' => 0]);

        return response()->json(null, 204);
    }
}
