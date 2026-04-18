<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CertPlantillaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = $request->get('query', '');
        $size = (int) $request->get('pageSize', 20);
        $page = (int) $request->get('pageIndex', 1);

        $q = DB::table('t_cert_plantilla');
        if ($query) {
            $q->where('nombre', 'like', "%{$query}%");
        }
        if ($request->has('tipo')) {
            $q->where('tipo', $request->get('tipo'));
        }
        if ($request->boolean('soloActivos', false)) {
            $q->where('estado', 'activo');
        }

        $total = $q->count();
        $data = $q->orderBy('nombre')->offset(($page - 1) * $size)->limit($size)->get();

        return response()->json(['data' => $data, 'total' => $total]);
    }

    public function show(int $id): JsonResponse
    {
        $row = DB::table('t_cert_plantilla')->find($id);
        if (! $row) {
            abort(404, 'Plantilla de certificado no encontrada');
        }
        $row->campos = DB::table('t_cert_plantilla_campo')
            ->where('plantilla_id', $id)
            ->orderBy('orden')
            ->get();

        return response()->json($row);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:300'],
            'tipo' => ['nullable', 'string', 'max:50'],
            'imagen_url' => ['required', 'string', 'max:500'],
            'ancho_px' => ['nullable', 'integer'],
            'alto_px' => ['nullable', 'integer'],
            'orientacion' => ['nullable', 'in:horizontal,vertical'],
            'formato_salida' => ['nullable', 'in:jpg,png,pdf'],
            'calidad_jpg' => ['nullable', 'integer', 'min:1', 'max:100'],
            'fuente_default' => ['nullable', 'string', 'max:100'],
            'color_default' => ['nullable', 'string', 'max:7'],
            'estado' => ['nullable', 'string', 'max:50'],
            'notas' => ['nullable', 'string'],
            'id_us_reg' => ['nullable', 'integer'],
        ]);
        $data['tipo'] = $data['tipo'] ?? 'aprobacion';
        $data['ancho_px'] = $request->integer('ancho_px', 3508);
        $data['alto_px'] = $request->integer('alto_px', 2480);
        $data['orientacion'] = $data['orientacion'] ?? 'horizontal';
        $data['formato_salida'] = $data['formato_salida'] ?? 'jpg';
        $data['calidad_jpg'] = $request->integer('calidad_jpg', 95);
        $data['estado'] = $data['estado'] ?? 'activo';
        $data['id_us_reg'] = $request->integer('id_us_reg', 0);
        $data['created_at'] = now();

        $id = DB::table('t_cert_plantilla')->insertGetId($data);
        $row = DB::table('t_cert_plantilla')->find($id);

        return response()->json($row, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = DB::table('t_cert_plantilla')->find($id);
        if (! $row) {
            abort(404, 'Plantilla de certificado no encontrada');
        }

        $data = $request->validate([
            'nombre' => ['sometimes', 'required', 'string', 'max:300'],
            'tipo' => ['nullable', 'string', 'max:50'],
            'imagen_url' => ['sometimes', 'required', 'string', 'max:500'],
            'ancho_px' => ['nullable', 'integer'],
            'alto_px' => ['nullable', 'integer'],
            'orientacion' => ['nullable', 'in:horizontal,vertical'],
            'formato_salida' => ['nullable', 'in:jpg,png,pdf'],
            'calidad_jpg' => ['nullable', 'integer', 'min:1', 'max:100'],
            'fuente_default' => ['nullable', 'string', 'max:100'],
            'color_default' => ['nullable', 'string', 'max:7'],
            'estado' => ['nullable', 'string', 'max:50'],
            'notas' => ['nullable', 'string'],
        ]);
        $data['updated_at'] = now();
        DB::table('t_cert_plantilla')->where('id', $id)->update($data);

        return response()->json(DB::table('t_cert_plantilla')->find($id));
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = DB::table('t_cert_plantilla')->where('id', $id)->delete();
        if (! $deleted) {
            abort(404, 'Plantilla de certificado no encontrada');
        }

        return response()->json(null, 204);
    }
}
