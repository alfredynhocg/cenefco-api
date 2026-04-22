<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CertificadoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CertificadoController extends Controller
{
    public function generarLote(Request $request, CertificadoService $service): JsonResponse
    {
        $data = $request->validate([
            'imparte_id' => ['required', 'integer'],
            'plantilla_id' => ['required', 'integer'],
        ]);

        try {
            $resultado = $service->generarLote((int) $data['imparte_id'], (int) $data['plantilla_id']);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($resultado, 200);
    }

    public function index(Request $request): JsonResponse
    {
        $query = $request->get('query', '');
        $size = (int) $request->get('pageSize', 20);
        $page = (int) $request->get('pageIndex', 1);

        $q = DB::table('t_certificado');
        if ($query) {
            $q->where(function ($sq) use ($query) {
                $sq->where('nombre_en_certificado', 'like', "%{$query}%")
                    ->orWhere('codigo_verificacion', 'like', "%{$query}%");
            });
        }
        if ($request->has('usuario_id')) {
            $q->where('usuario_id', (int) $request->get('usuario_id'));
        }
        if ($request->has('imparte_id')) {
            $q->where('imparte_id', (int) $request->get('imparte_id'));
        }
        if ($request->has('estado')) {
            $q->where('estado', $request->get('estado'));
        }

        $total = $q->count();
        $data = $q->orderByDesc('id')->offset(($page - 1) * $size)->limit($size)->get();

        return response()->json(['data' => $data, 'total' => $total]);
    }

    public function show(int $id): JsonResponse
    {
        $row = DB::table('t_certificado')->find($id);
        if (! $row) {
            abort(404, 'Certificado no encontrado');
        }

        return response()->json($row);
    }

    public function showByCode(string $codigo): JsonResponse
    {
        $row = DB::table('t_certificado')->where('codigo_verificacion', $codigo)->first();
        if (! $row) {
            abort(404, 'Certificado no encontrado');
        }

        return response()->json($row);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'lista_aprobado_id' => ['required', 'integer'],
            'plantilla_id' => ['required', 'integer'],
            'usuario_id' => ['required', 'integer'],
            'imparte_id' => ['required', 'integer'],
            'nombre_en_certificado' => ['required', 'string', 'max:300'],
            'programa_en_certificado' => ['required', 'string', 'max:300'],
            'condicion' => ['required', 'string', 'max:50'],
            'nota_final' => ['nullable', 'numeric'],
            'horas_academicas' => ['nullable', 'integer'],
            'fecha_inicio_curso' => ['nullable', 'date'],
            'fecha_fin_curso' => ['nullable', 'date'],
            'codigo_verificacion' => ['required', 'string', 'max:50', 'unique:t_certificado,codigo_verificacion'],
            'qr_url' => ['nullable', 'string', 'max:500'],
            'archivo_url' => ['nullable', 'string', 'max:500'],
            'archivo_miniatura_url' => ['nullable', 'string', 'max:255'],
            'estado' => ['nullable', 'string', 'max:50'],
        ]);
        $data['estado'] = $data['estado'] ?? 'generado';
        $data['created_at'] = now();

        $id = DB::table('t_certificado')->insertGetId($data);
        $row = DB::table('t_certificado')->find($id);

        return response()->json($row, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = DB::table('t_certificado')->find($id);
        if (! $row) {
            abort(404, 'Certificado no encontrado');
        }

        $data = $request->validate([
            'nombre_en_certificado' => ['sometimes', 'required', 'string', 'max:300'],
            'programa_en_certificado' => ['sometimes', 'required', 'string', 'max:300'],
            'qr_url' => ['nullable', 'string', 'max:500'],
            'archivo_url' => ['nullable', 'string', 'max:500'],
            'archivo_miniatura_url' => ['nullable', 'string', 'max:255'],
            'estado' => ['nullable', 'string', 'max:50'],
            'motivo_anulacion' => ['nullable', 'string'],
            'anulado_por' => ['nullable', 'integer'],
        ]);
        $data['updated_at'] = now();
        DB::table('t_certificado')->where('id', $id)->update($data);

        return response()->json(DB::table('t_certificado')->find($id));
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = DB::table('t_certificado')->where('id', $id)->delete();
        if (! $deleted) {
            abort(404, 'Certificado no encontrado');
        }

        return response()->json(null, 204);
    }
}
