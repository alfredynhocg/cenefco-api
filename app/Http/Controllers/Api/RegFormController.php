<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegFormController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = $request->get('query', '');
        $size = (int) $request->get('pageSize', 50);
        $page = (int) $request->get('pageIndex', 1);

        $q = DB::table('t_regform');
        if ($query) {
            $q->where('nombreform', 'like', "%{$query}%");
        }
        if ($request->has('id_regcp')) {
            $q->where('id_regcp', (int) $request->get('id_regcp'));
        }
        if ($request->has('id_niv')) {
            $q->where('id_niv', (int) $request->get('id_niv'));
        }
        if (! $request->boolean('conInactivos', false)) {
            $q->where('estado', 1);
        }

        $total = $q->count();
        $data = $q->orderBy('nombreform')->offset(($page - 1) * $size)->limit($size)->get();

        return response()->json(['data' => $data, 'total' => $total]);
    }

    public function show(int $id): JsonResponse
    {
        $row = DB::table('t_regform')->where('id_regform', $id)->first();
        if (! $row) {
            abort(404, 'RegForm no encontrado');
        }

        return response()->json($row);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_regform' => ['required', 'integer'],
            'id_us_reg' => ['nullable', 'integer'],
            'num_regform' => ['nullable', 'integer'],
            'id_regcp' => ['nullable', 'integer'],
            'nombreform' => ['required', 'string', 'max:200'],
            'id_niv' => ['nullable', 'integer'],
            'estado' => ['nullable', 'integer'],
        ]);
        $data['id_us_reg'] = $request->integer('id_us_reg', 0);
        $data['num_regform'] = $request->integer('num_regform', 0);
        $data['estado'] = $request->integer('estado', 1);
        $data['fecha_reg'] = now();

        DB::table('t_regform')->insert($data);

        return response()->json(DB::table('t_regform')->where('id_regform', $data['id_regform'])->first(), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = DB::table('t_regform')->where('id_regform', $id)->first();
        if (! $row) {
            abort(404, 'RegForm no encontrado');
        }

        $data = $request->validate([
            'id_regcp' => ['nullable', 'integer'],
            'nombreform' => ['sometimes', 'required', 'string', 'max:200'],
            'id_niv' => ['nullable', 'integer'],
            'estado' => ['nullable', 'integer'],
        ]);
        DB::table('t_regform')->where('id_regform', $id)->update($data);

        return response()->json(DB::table('t_regform')->where('id_regform', $id)->first());
    }

    public function destroy(int $id): JsonResponse
    {
        DB::table('t_regform')->where('id_regform', $id)->update(['estado' => 0]);

        return response()->json(null, 204);
    }
}
