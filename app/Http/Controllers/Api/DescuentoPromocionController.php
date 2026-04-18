<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DescuentoPromocionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = $request->get('query', '');
        $size = (int) $request->get('pageSize', 20);
        $page = (int) $request->get('pageIndex', 1);

        $q = DB::table('web_descuento_promocion');
        if ($query) {
            $q->where(function ($sq) use ($query) {
                $sq->where('codigo', 'like', "%{$query}%")
                    ->orWhere('nombre', 'like', "%{$query}%");
            });
        }
        if ($request->has('activo')) {
            $q->where('activo', $request->boolean('activo'));
        }
        if ($request->has('tipo_descuento')) {
            $q->where('tipo_descuento', $request->get('tipo_descuento'));
        }

        $total = $q->count();
        $data = $q->orderByDesc('id')->offset(($page - 1) * $size)->limit($size)->get();

        return response()->json(['data' => $data, 'total' => $total]);
    }

    public function show(int $id): JsonResponse
    {
        $row = DB::table('web_descuento_promocion')->find($id);
        if (! $row) {
            abort(404, 'Descuento/Promoción no encontrado');
        }

        return response()->json($row);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'codigo' => ['required', 'string', 'max:50', 'unique:web_descuento_promocion,codigo'],
            'nombre' => ['required', 'string', 'max:200'],
            'descripcion' => ['nullable', 'string'],
            'tipo_descuento' => ['nullable', 'in:porcentaje,monto_fijo'],
            'valor' => ['required', 'numeric', 'min:0'],
            'monto_minimo' => ['nullable', 'numeric', 'min:0'],
            'usos_maximos' => ['nullable', 'integer', 'min:0'],
            'usos_por_usuario' => ['nullable', 'integer', 'min:1'],
            'programa_id' => ['nullable', 'integer'],
            'activo' => ['nullable', 'boolean'],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date'],
        ]);
        $data['tipo_descuento'] = $data['tipo_descuento'] ?? 'porcentaje';
        $data['usos_actuales'] = 0;
        $data['usos_por_usuario'] = $request->integer('usos_por_usuario', 1);
        $data['activo'] = $request->boolean('activo', true);
        $data['created_at'] = now();

        $id = DB::table('web_descuento_promocion')->insertGetId($data);
        $row = DB::table('web_descuento_promocion')->find($id);

        return response()->json($row, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = DB::table('web_descuento_promocion')->find($id);
        if (! $row) {
            abort(404, 'Descuento/Promoción no encontrado');
        }

        $data = $request->validate([
            'nombre' => ['sometimes', 'required', 'string', 'max:200'],
            'descripcion' => ['nullable', 'string'],
            'tipo_descuento' => ['nullable', 'in:porcentaje,monto_fijo'],
            'valor' => ['sometimes', 'required', 'numeric', 'min:0'],
            'monto_minimo' => ['nullable', 'numeric', 'min:0'],
            'usos_maximos' => ['nullable', 'integer', 'min:0'],
            'usos_por_usuario' => ['nullable', 'integer', 'min:1'],
            'programa_id' => ['nullable', 'integer'],
            'activo' => ['nullable', 'boolean'],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date'],
        ]);
        $data['updated_at'] = now();
        DB::table('web_descuento_promocion')->where('id', $id)->update($data);

        return response()->json(DB::table('web_descuento_promocion')->find($id));
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = DB::table('web_descuento_promocion')->where('id', $id)->delete();
        if (! $deleted) {
            abort(404, 'Descuento/Promoción no encontrado');
        }

        return response()->json(null, 204);
    }
}
