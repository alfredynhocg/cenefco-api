<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $size = (int) $request->get('pageSize', 50);
        $page = (int) $request->get('pageIndex', 1);

        $q = DB::table('t_nota');
        if ($request->has('id_us')) {
            $q->where('id_us', (int) $request->get('id_us'));
        }
        if ($request->has('id_imp')) {
            $q->where('id_imp', (int) $request->get('id_imp'));
        }
        if ($request->has('id_mat')) {
            $q->where('id_mat', (int) $request->get('id_mat'));
        }
        if ($request->has('periodo')) {
            $q->where('periodo', $request->get('periodo'));
        }
        if ($request->has('gestion')) {
            $q->where('gestion', $request->get('gestion'));
        }
        if (! $request->boolean('conInactivos', false)) {
            $q->where('estado', 1);
        }

        $total = $q->count();
        $data = $q->orderByDesc('id_not')->offset(($page - 1) * $size)->limit($size)->get();

        return response()->json(['data' => $data, 'total' => $total]);
    }

    public function show(int $id): JsonResponse
    {
        $row = DB::table('t_nota')->where('id_not', $id)->first();
        if (! $row) {
            abort(404, 'Nota no encontrada');
        }

        return response()->json($row);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_not' => ['required', 'integer'],
            'id_us_reg' => ['nullable', 'integer'],
            'periodo' => ['nullable', 'string', 'max:200'],
            'gestion' => ['nullable', 'string', 'max:10'],
            'id_imp' => ['required', 'integer'],
            'id_us' => ['required', 'integer'],
            'id_mat' => ['nullable', 'integer'],
            'nota' => ['required', 'integer'],
            'nota_seg' => ['nullable', 'integer'],
            'paralelo' => ['nullable', 'string', 'max:200'],
            'estado' => ['nullable', 'integer'],
        ]);
        $data['id_us_reg'] = $request->integer('id_us_reg', 0);
        $data['nota_seg'] = $request->integer('nota_seg', 0);
        $data['mostrarcert_notas'] = 1;
        $data['estado'] = $request->integer('estado', 1);
        $data['fecha_reg'] = now();

        DB::table('t_nota')->insert($data);

        return response()->json(DB::table('t_nota')->where('id_not', $data['id_not'])->first(), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = DB::table('t_nota')->where('id_not', $id)->first();
        if (! $row) {
            abort(404, 'Nota no encontrada');
        }

        $data = $request->validate([
            'nota' => ['sometimes', 'required', 'integer'],
            'nota_seg' => ['nullable', 'integer'],
            'paralelo' => ['nullable', 'string', 'max:200'],
            'mostrarcert_notas' => ['nullable', 'integer'],
            'estado' => ['nullable', 'integer'],
        ]);
        DB::table('t_nota')->where('id_not', $id)->update($data);

        return response()->json(DB::table('t_nota')->where('id_not', $id)->first());
    }

    public function destroy(int $id): JsonResponse
    {
        DB::table('t_nota')->where('id_not', $id)->update(['estado' => 0]);

        return response()->json(null, 204);
    }
}
