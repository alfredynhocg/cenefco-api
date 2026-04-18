<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PopupController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $size = (int) $request->get('pageSize', 20);
        $page = (int) $request->get('pageIndex', 1);

        $q = DB::table('web_popup');
        if ($request->has('activo')) {
            $q->where('activo', $request->boolean('activo'));
        }

        $total = $q->count();
        $data = $q->orderByDesc('id')->offset(($page - 1) * $size)->limit($size)->get();

        return response()->json(['data' => $data, 'total' => $total]);
    }

    public function show(int $id): JsonResponse
    {
        $row = DB::table('web_popup')->find($id);
        if (! $row) {
            abort(404, 'Popup no encontrado');
        }

        return response()->json($row);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'titulo' => ['nullable', 'string', 'max:300'],
            'contenido' => ['nullable', 'string'],
            'imagen_url' => ['nullable', 'string', 'max:255'],
            'enlace_url' => ['nullable', 'string', 'max:255'],
            'enlace_texto' => ['nullable', 'string', 'max:100'],
            'posicion' => ['nullable', 'string', 'max:50'],
            'delay_segundos' => ['nullable', 'integer'],
            'mostrar_una_vez_sesion' => ['nullable', 'boolean'],
            'mostrar_una_vez_siempre' => ['nullable', 'boolean'],
            'paginas_mostrar' => ['nullable', 'string', 'max:500'],
            'activo' => ['nullable', 'boolean'],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date'],
        ]);
        $data['posicion'] = $data['posicion'] ?? 'center';
        $data['delay_segundos'] = $request->integer('delay_segundos', 3);
        $data['mostrar_una_vez_sesion'] = $request->boolean('mostrar_una_vez_sesion', true);
        $data['mostrar_una_vez_siempre'] = $request->boolean('mostrar_una_vez_siempre', false);
        $data['activo'] = $request->boolean('activo', false);
        $data['created_at'] = now();

        $id = DB::table('web_popup')->insertGetId($data);
        $row = DB::table('web_popup')->find($id);

        return response()->json($row, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = DB::table('web_popup')->find($id);
        if (! $row) {
            abort(404, 'Popup no encontrado');
        }

        $data = $request->validate([
            'titulo' => ['nullable', 'string', 'max:300'],
            'contenido' => ['nullable', 'string'],
            'imagen_url' => ['nullable', 'string', 'max:255'],
            'enlace_url' => ['nullable', 'string', 'max:255'],
            'enlace_texto' => ['nullable', 'string', 'max:100'],
            'posicion' => ['nullable', 'string', 'max:50'],
            'delay_segundos' => ['nullable', 'integer'],
            'mostrar_una_vez_sesion' => ['nullable', 'boolean'],
            'mostrar_una_vez_siempre' => ['nullable', 'boolean'],
            'paginas_mostrar' => ['nullable', 'string', 'max:500'],
            'activo' => ['nullable', 'boolean'],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date'],
        ]);
        $data['updated_at'] = now();
        DB::table('web_popup')->where('id', $id)->update($data);

        return response()->json(DB::table('web_popup')->find($id));
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = DB::table('web_popup')->where('id', $id)->delete();
        if (! $deleted) {
            abort(404, 'Popup no encontrado');
        }

        return response()->json(null, 204);
    }
}
