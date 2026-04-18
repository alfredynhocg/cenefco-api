<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AcreditacionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = $request->get('query', '');
        $size = (int) $request->get('pageSize', 20);
        $page = (int) $request->get('pageIndex', 1);

        $q = DB::table('web_acreditacion');
        if ($query) {
            $q->where(function ($sq) use ($query) {
                $sq->where('nombre', 'like', "%{$query}%")
                    ->orWhere('entidad_otorgante', 'like', "%{$query}%");
            });
        }
        if ($request->has('tipo')) {
            $q->where('tipo', $request->get('tipo'));
        }
        if ($request->boolean('soloActivos', false)) {
            $q->where('activo', true);
        }

        $total = $q->count();
        $data = $q->orderBy('orden')->orderBy('nombre')->offset(($page - 1) * $size)->limit($size)->get();

        return response()->json(['data' => $data, 'total' => $total]);
    }

    public function show(int $id): JsonResponse
    {
        $row = DB::table('web_acreditacion')->find($id);
        if (! $row) {
            abort(404, 'Acreditación no encontrada');
        }

        return response()->json($row);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:300'],
            'entidad_otorgante' => ['required', 'string', 'max:200'],
            'tipo' => ['nullable', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string'],
            'logo_url' => ['nullable', 'string', 'max:255'],
            'logo_alt' => ['nullable', 'string', 'max:255'],
            'url_verificacion' => ['nullable', 'string', 'max:255'],
            'fecha_obtencion' => ['nullable', 'date'],
            'fecha_vencimiento' => ['nullable', 'date'],
            'orden' => ['nullable', 'integer'],
            'activo' => ['nullable', 'boolean'],
        ]);
        $data['orden'] = $request->integer('orden', 0);
        $data['activo'] = $request->boolean('activo', true);
        $data['created_at'] = now();

        $id = DB::table('web_acreditacion')->insertGetId($data);
        $row = DB::table('web_acreditacion')->find($id);

        return response()->json($row, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = DB::table('web_acreditacion')->find($id);
        if (! $row) {
            abort(404, 'Acreditación no encontrada');
        }

        $data = $request->validate([
            'nombre' => ['sometimes', 'required', 'string', 'max:300'],
            'entidad_otorgante' => ['sometimes', 'required', 'string', 'max:200'],
            'tipo' => ['nullable', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string'],
            'logo_url' => ['nullable', 'string', 'max:255'],
            'logo_alt' => ['nullable', 'string', 'max:255'],
            'url_verificacion' => ['nullable', 'string', 'max:255'],
            'fecha_obtencion' => ['nullable', 'date'],
            'fecha_vencimiento' => ['nullable', 'date'],
            'orden' => ['nullable', 'integer'],
            'activo' => ['nullable', 'boolean'],
        ]);
        $data['updated_at'] = now();
        DB::table('web_acreditacion')->where('id', $id)->update($data);

        return response()->json(DB::table('web_acreditacion')->find($id));
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = DB::table('web_acreditacion')->where('id', $id)->delete();
        if (! $deleted) {
            abort(404, 'Acreditación no encontrada');
        }

        return response()->json(null, 204);
    }
}
