<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ImparteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = $request->get('query', '');
        $size = (int) $request->get('pageSize', 30);
        $page = (int) $request->get('pageIndex', 1);

        $q = DB::table('t_imparte');
        if ($query) {
            $q->where(function ($sq) use ($query) {
                $sq->where('periodo', 'like', "%{$query}%")
                    ->orWhere('paralelo', 'like', "%{$query}%");
            });
        }
        if ($request->has('periodo')) {
            $q->where('periodo', $request->get('periodo'));
        }
        if ($request->has('gestion')) {
            $q->where('gestion', $request->get('gestion'));
        }
        if ($request->has('id_mat')) {
            $q->where('id_mat', (int) $request->get('id_mat'));
        }
        if ($request->has('id_us')) {
            $q->where('id_us', (int) $request->get('id_us'));
        }
        if (! $request->boolean('conInactivos', false)) {
            $q->where('estado', 1);
        }

        $total = $q->count();
        $data = $q->orderByDesc('id_imp')->offset(($page - 1) * $size)->limit($size)->get();

        return response()->json(['data' => $data, 'total' => $total]);
    }

    public function show(int $id): JsonResponse
    {
        $row = DB::table('t_imparte')->where('id_imp', $id)->first();
        if (! $row) {
            abort(404, 'Impartición no encontrada');
        }

        return response()->json($row);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_imp' => ['required', 'integer'],
            'id_us_reg' => ['nullable', 'integer'],
            'num_imp' => ['nullable', 'integer'],
            'periodo' => ['nullable', 'string', 'max:30'],
            'gestion' => ['nullable', 'string', 'max:10'],
            'id_us' => ['nullable', 'integer'],
            'id_mat' => ['nullable', 'integer'],
            'paralelo' => ['nullable', 'string', 'max:200'],
            'cupo' => ['nullable', 'string', 'max:200'],
            'observacion_imp' => ['nullable', 'string'],
            'nro_resolucion_hcu' => ['nullable', 'string', 'max:200'],
            'id_moodle' => ['nullable', 'integer'],
            'estado' => ['nullable', 'integer'],
        ]);
        $data['id_us_reg'] = $request->integer('id_us_reg', 0);
        $data['num_imp'] = $request->integer('num_imp', 0);
        $data['estado'] = $request->integer('estado', 1);
        $data['fecha_reg'] = now();

        DB::table('t_imparte')->insert($data);

        return response()->json(DB::table('t_imparte')->where('id_imp', $data['id_imp'])->first(), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = DB::table('t_imparte')->where('id_imp', $id)->first();
        if (! $row) {
            abort(404, 'Impartición no encontrada');
        }

        $data = $request->validate([
            'periodo' => ['nullable', 'string', 'max:30'],
            'gestion' => ['nullable', 'string', 'max:10'],
            'id_us' => ['nullable', 'integer'],
            'id_mat' => ['nullable', 'integer'],
            'paralelo' => ['nullable', 'string', 'max:200'],
            'cupo' => ['nullable', 'string', 'max:200'],
            'observacion_imp' => ['nullable', 'string'],
            'nro_resolucion_hcu' => ['nullable', 'string', 'max:200'],
            'estado' => ['nullable', 'integer'],
        ]);
        DB::table('t_imparte')->where('id_imp', $id)->update($data);

        return response()->json(DB::table('t_imparte')->where('id_imp', $id)->first());
    }

    public function destroy(int $id): JsonResponse
    {
        DB::table('t_imparte')->where('id_imp', $id)->update(['estado' => 0]);

        return response()->json(null, 204);
    }
}
