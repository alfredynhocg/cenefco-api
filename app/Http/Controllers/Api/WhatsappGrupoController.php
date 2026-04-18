<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WhatsappGrupoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $size = (int) $request->get('pageSize', 50);
        $page = (int) $request->get('pageIndex', 1);

        $q = DB::table('web_whatsapp_grupo');
        if ($request->has('imparte_id')) {
            $q->where('imparte_id', (int) $request->get('imparte_id'));
        }
        if ($request->boolean('soloActivos', false)) {
            $q->where('activo', true);
        }

        $total = $q->count();
        $data = $q->orderBy('orden')->orderByDesc('id')->offset(($page - 1) * $size)->limit($size)->get();

        return response()->json(['data' => $data, 'total' => $total]);
    }

    public function show(int $id): JsonResponse
    {
        $row = DB::table('web_whatsapp_grupo')->find($id);
        if (! $row) {
            abort(404, 'Grupo de WhatsApp no encontrado');
        }

        return response()->json($row);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'imparte_id' => ['required', 'integer'],
            'nombre' => ['required', 'string', 'max:200'],
            'enlace_invitacion' => ['required', 'string', 'max:500'],
            'capacidad_maxima' => ['nullable', 'integer'],
            'miembros_actuales' => ['nullable', 'integer'],
            'descripcion' => ['nullable', 'string', 'max:300'],
            'activo' => ['nullable', 'boolean'],
            'orden' => ['nullable', 'integer'],
            'fecha_expiracion_enlace' => ['nullable', 'date'],
        ]);
        $data['miembros_actuales'] = $request->integer('miembros_actuales', 0);
        $data['activo'] = $request->boolean('activo', true);
        $data['orden'] = $request->integer('orden', 0);
        $data['created_at'] = now();

        $id = DB::table('web_whatsapp_grupo')->insertGetId($data);
        $row = DB::table('web_whatsapp_grupo')->find($id);

        return response()->json($row, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = DB::table('web_whatsapp_grupo')->find($id);
        if (! $row) {
            abort(404, 'Grupo de WhatsApp no encontrado');
        }

        $data = $request->validate([
            'imparte_id' => ['sometimes', 'required', 'integer'],
            'nombre' => ['sometimes', 'required', 'string', 'max:200'],
            'enlace_invitacion' => ['sometimes', 'required', 'string', 'max:500'],
            'capacidad_maxima' => ['nullable', 'integer'],
            'miembros_actuales' => ['nullable', 'integer'],
            'descripcion' => ['nullable', 'string', 'max:300'],
            'activo' => ['nullable', 'boolean'],
            'orden' => ['nullable', 'integer'],
            'fecha_expiracion_enlace' => ['nullable', 'date'],
        ]);
        $data['updated_at'] = now();
        DB::table('web_whatsapp_grupo')->where('id', $id)->update($data);

        return response()->json(DB::table('web_whatsapp_grupo')->find($id));
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = DB::table('web_whatsapp_grupo')->where('id', $id)->delete();
        if (! $deleted) {
            abort(404, 'Grupo de WhatsApp no encontrado');
        }

        return response()->json(null, 204);
    }
}
