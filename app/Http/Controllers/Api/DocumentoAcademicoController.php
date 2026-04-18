<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DocumentoAcademicoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $size = (int) $request->get('pageSize', 50);
        $page = (int) $request->get('pageIndex', 1);

        $q = DB::table('t_documento');
        if ($request->has('id_us')) {
            $q->where('id_us', (int) $request->get('id_us'));
        }
        if ($request->has('id_fechapago')) {
            $q->where('id_fechapago', (int) $request->get('id_fechapago'));
        }
        if ($request->has('id_fechadoc')) {
            $q->where('id_fechadoc', (int) $request->get('id_fechadoc'));
        }
        if (! $request->boolean('conInactivos', false)) {
            $q->where('estado', 1);
        }

        $total = $q->count();
        $data = $q->orderByDesc('id_documento')->offset(($page - 1) * $size)->limit($size)->get();

        return response()->json(['data' => $data, 'total' => $total]);
    }

    public function show(int $id): JsonResponse
    {
        $row = DB::table('t_documento')->where('id_documento', $id)->first();
        if (! $row) {
            abort(404, 'Documento no encontrado');
        }

        return response()->json($row);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_documento' => ['required', 'integer'],
            'id_us_reg' => ['nullable', 'integer'],
            'num_documento' => ['nullable', 'integer'],
            'id_us' => ['required', 'integer'],
            'id_fechapago' => ['nullable', 'integer'],
            'id_fechadoc' => ['nullable', 'integer'],
            'fecha_dejo_fisico' => ['nullable', 'date'],
            'dejo_documento_fisico' => ['nullable', 'integer'],
            'documento_digital' => ['nullable', 'string', 'max:200'],
            'observacion_doc' => ['nullable', 'string'],
            'estado' => ['nullable', 'integer'],
        ]);
        $data['id_us_reg'] = $request->integer('id_us_reg', 0);
        $data['num_documento'] = $request->integer('num_documento', 0);
        $data['estado'] = $request->integer('estado', 1);
        $data['fecha_reg'] = now();

        DB::table('t_documento')->insert($data);

        return response()->json(DB::table('t_documento')->where('id_documento', $data['id_documento'])->first(), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = DB::table('t_documento')->where('id_documento', $id)->first();
        if (! $row) {
            abort(404, 'Documento no encontrado');
        }

        $data = $request->validate([
            'id_fechapago' => ['nullable', 'integer'],
            'id_fechadoc' => ['nullable', 'integer'],
            'fecha_dejo_fisico' => ['nullable', 'date'],
            'dejo_documento_fisico' => ['nullable', 'integer'],
            'documento_digital' => ['nullable', 'string', 'max:200'],
            'observacion_doc' => ['nullable', 'string'],
            'estado' => ['nullable', 'integer'],
        ]);
        DB::table('t_documento')->where('id_documento', $id)->update($data);

        return response()->json(DB::table('t_documento')->where('id_documento', $id)->first());
    }

    public function destroy(int $id): JsonResponse
    {
        DB::table('t_documento')->where('id_documento', $id)->update(['estado' => 0]);

        return response()->json(null, 204);
    }
}
