<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RequisitoAcademicoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $size = (int) $request->get('pageSize', 100);
        $page = (int) $request->get('pageIndex', 1);

        $q = DB::table('t_requisito');
        if ($request->has('id_mat')) {
            $q->where('id_mat', (int) $request->get('id_mat'));
        }
        if (! $request->boolean('conInactivos', false)) {
            $q->where('estado', 1);
        }

        $total = $q->count();
        $data = $q->orderBy('id_req')->offset(($page - 1) * $size)->limit($size)->get();

        return response()->json(['data' => $data, 'total' => $total]);
    }

    public function show(int $id): JsonResponse
    {
        $row = DB::table('t_requisito')->where('id_req', $id)->first();
        if (! $row) {
            abort(404, 'Requisito no encontrado');
        }

        return response()->json($row);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_req' => ['required', 'integer'],
            'id_mat' => ['required', 'integer'],
            'id_us_reg' => ['nullable', 'integer'],
            'num_req' => ['nullable', 'integer'],
            'pre_id_mat' => ['nullable', 'integer'],
            'todos1a6' => ['nullable', 'string', 'max:200'],
            'modalidad_req' => ['nullable', 'string', 'max:200'],
            'estado' => ['nullable', 'integer'],
        ]);
        $data['id_us_reg'] = $request->integer('id_us_reg', 0);
        $data['num_req'] = $request->integer('num_req', 0);
        $data['estado'] = $request->integer('estado', 1);
        $data['fecha_reg'] = now();

        DB::table('t_requisito')->insert($data);

        return response()->json(DB::table('t_requisito')->where('id_req', $data['id_req'])->first(), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = DB::table('t_requisito')->where('id_req', $id)->first();
        if (! $row) {
            abort(404, 'Requisito no encontrado');
        }

        $data = $request->validate([
            'pre_id_mat' => ['nullable', 'integer'],
            'todos1a6' => ['nullable', 'string', 'max:200'],
            'modalidad_req' => ['nullable', 'string', 'max:200'],
            'estado' => ['nullable', 'integer'],
        ]);
        DB::table('t_requisito')->where('id_req', $id)->update($data);

        return response()->json(DB::table('t_requisito')->where('id_req', $id)->first());
    }

    public function destroy(int $id): JsonResponse
    {
        DB::table('t_requisito')->where('id_req', $id)->update(['estado' => 0]);

        return response()->json(null, 204);
    }
}
