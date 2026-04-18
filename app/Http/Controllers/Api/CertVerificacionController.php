<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CertVerificacionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $size = (int) $request->get('pageSize', 30);
        $page = (int) $request->get('pageIndex', 1);

        $q = DB::table('t_cert_verificacion');
        if ($request->has('certificado_id')) {
            $q->where('certificado_id', (int) $request->get('certificado_id'));
        }
        if ($request->has('codigo_consultado')) {
            $q->where('codigo_consultado', $request->get('codigo_consultado'));
        }
        if ($request->has('resultado')) {
            $q->where('resultado', $request->get('resultado'));
        }

        $total = $q->count();
        $data = $q->orderByDesc('created_at')->offset(($page - 1) * $size)->limit($size)->get();

        return response()->json(['data' => $data, 'total' => $total]);
    }

    public function show(int $id): JsonResponse
    {
        $row = DB::table('t_cert_verificacion')->find($id);
        if (! $row) {
            abort(404, 'Verificación no encontrada');
        }

        return response()->json($row);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'certificado_id' => ['nullable', 'integer'],
            'codigo_consultado' => ['required', 'string', 'max:100'],
            'resultado' => ['required', 'string', 'max:20'],
            'ip_origen' => ['nullable', 'string', 'max:45'],
            'user_agent' => ['nullable', 'string', 'max:500'],
            'pais' => ['nullable', 'string', 'max:100'],
        ]);
        $data['ip_origen'] = $data['ip_origen'] ?? $request->ip();
        $data['user_agent'] = $data['user_agent'] ?? $request->userAgent();
        $data['created_at'] = now();

        $id = DB::table('t_cert_verificacion')->insertGetId($data);
        $row = DB::table('t_cert_verificacion')->find($id);

        return response()->json($row, 201);
    }
}
