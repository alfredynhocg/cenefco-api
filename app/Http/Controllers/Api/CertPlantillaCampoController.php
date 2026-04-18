<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CertPlantillaCampoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $plantillaId = (int) $request->get('plantilla_id', 0);
        $size = (int) $request->get('pageSize', 100);
        $page = (int) $request->get('pageIndex', 1);

        $q = DB::table('t_cert_plantilla_campo');
        if ($plantillaId) {
            $q->where('plantilla_id', $plantillaId);
        }
        if ($request->boolean('soloActivos', false)) {
            $q->where('activo', true);
        }

        $total = $q->count();
        $data = $q->orderBy('orden')->orderBy('clave')->offset(($page - 1) * $size)->limit($size)->get();

        return response()->json(['data' => $data, 'total' => $total]);
    }

    public function show(int $id): JsonResponse
    {
        $row = DB::table('t_cert_plantilla_campo')->find($id);
        if (! $row) {
            abort(404, 'Campo de plantilla no encontrado');
        }

        return response()->json($row);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'plantilla_id' => ['required', 'integer'],
            'clave' => ['required', 'string', 'max:100'],
            'etiqueta' => ['required', 'string', 'max:200'],
            'tipo' => ['nullable', 'string', 'max:50'],
            'pos_x_pct' => ['required', 'numeric'],
            'pos_y_pct' => ['required', 'numeric'],
            'ancho_pct' => ['nullable', 'numeric'],
            'alto_pct' => ['nullable', 'numeric'],
            'fuente' => ['nullable', 'string', 'max:100'],
            'tamano_pt' => ['nullable', 'integer'],
            'color' => ['nullable', 'string', 'max:7'],
            'alineacion' => ['nullable', 'in:left,center,right'],
            'negrita' => ['nullable', 'boolean'],
            'cursiva' => ['nullable', 'boolean'],
            'mayusculas' => ['nullable', 'string', 'max:20'],
            'valor_fijo' => ['nullable', 'string', 'max:300'],
            'activo' => ['nullable', 'boolean'],
            'orden' => ['nullable', 'integer'],
        ]);
        $data['tipo'] = $data['tipo'] ?? 'texto';
        $data['tamano_pt'] = $request->integer('tamano_pt', 36);
        $data['color'] = $data['color'] ?? '#000000';
        $data['alineacion'] = $data['alineacion'] ?? 'center';
        $data['negrita'] = $request->boolean('negrita', false);
        $data['cursiva'] = $request->boolean('cursiva', false);
        $data['mayusculas'] = $data['mayusculas'] ?? 'none';
        $data['activo'] = $request->boolean('activo', true);
        $data['orden'] = $request->integer('orden', 0);

        $id = DB::table('t_cert_plantilla_campo')->insertGetId($data);
        $row = DB::table('t_cert_plantilla_campo')->find($id);

        return response()->json($row, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = DB::table('t_cert_plantilla_campo')->find($id);
        if (! $row) {
            abort(404, 'Campo de plantilla no encontrado');
        }

        $data = $request->validate([
            'clave' => ['sometimes', 'required', 'string', 'max:100'],
            'etiqueta' => ['sometimes', 'required', 'string', 'max:200'],
            'tipo' => ['nullable', 'string', 'max:50'],
            'pos_x_pct' => ['sometimes', 'required', 'numeric'],
            'pos_y_pct' => ['sometimes', 'required', 'numeric'],
            'ancho_pct' => ['nullable', 'numeric'],
            'alto_pct' => ['nullable', 'numeric'],
            'fuente' => ['nullable', 'string', 'max:100'],
            'tamano_pt' => ['nullable', 'integer'],
            'color' => ['nullable', 'string', 'max:7'],
            'alineacion' => ['nullable', 'in:left,center,right'],
            'negrita' => ['nullable', 'boolean'],
            'cursiva' => ['nullable', 'boolean'],
            'mayusculas' => ['nullable', 'string', 'max:20'],
            'valor_fijo' => ['nullable', 'string', 'max:300'],
            'activo' => ['nullable', 'boolean'],
            'orden' => ['nullable', 'integer'],
        ]);
        DB::table('t_cert_plantilla_campo')->where('id', $id)->update($data);

        return response()->json(DB::table('t_cert_plantilla_campo')->find($id));
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = DB::table('t_cert_plantilla_campo')->where('id', $id)->delete();
        if (! $deleted) {
            abort(404, 'Campo de plantilla no encontrado');
        }

        return response()->json(null, 204);
    }
}
