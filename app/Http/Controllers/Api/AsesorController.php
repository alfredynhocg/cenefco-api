<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AsesorController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = DB::table('web_asesor');

        if ($search = $request->get('query')) {
            $q->where(function ($sub) use ($search) {
                $sub->where('nombre', 'like', "%{$search}%")
                    ->orWhere('telefono', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->has('activo')) {
            $q->where('activo', (bool) $request->get('activo'));
        }

        $pageSize = (int) $request->get('pageSize', 15);
        $pageIndex = (int) $request->get('pageIndex', 1);
        $total = $q->count();
        $items = (clone $q)->orderBy('nombre')->offset(($pageIndex - 1) * $pageSize)->limit($pageSize)->get();

        return response()->json(['data' => $items, 'total' => $total]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:200'],
            'telefono' => ['required', 'string', 'max:30', 'unique:web_asesor,telefono'],
            'email' => ['nullable', 'email', 'max:100'],
            'especialidad' => ['nullable', 'string', 'max:200'],
            'disponible' => ['boolean'],
            'activo' => ['boolean'],
        ]);

        $data['created_at'] = now()->toDateTimeString();
        $data['updated_at'] = now()->toDateTimeString();

        $id = DB::table('web_asesor')->insertGetId($data);

        return response()->json(DB::table('web_asesor')->where('id', $id)->first(), 201);
    }

    public function show(int $id): JsonResponse
    {
        $item = DB::table('web_asesor')->where('id', $id)->first();
        if (! $item) {
            abort(404, 'Asesor no encontrado');
        }

        return response()->json($item);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        if (! DB::table('web_asesor')->where('id', $id)->exists()) {
            abort(404, 'Asesor no encontrado');
        }

        $data = $request->validate([
            'nombre' => ['sometimes', 'string', 'max:200'],
            'telefono' => ['sometimes', 'string', 'max:30', "unique:web_asesor,telefono,{$id}"],
            'email' => ['nullable', 'email', 'max:100'],
            'especialidad' => ['nullable', 'string', 'max:200'],
            'disponible' => ['boolean'],
            'activo' => ['boolean'],
        ]);

        $data['updated_at'] = now()->toDateTimeString();
        DB::table('web_asesor')->where('id', $id)->update($data);

        return response()->json(DB::table('web_asesor')->where('id', $id)->first());
    }

    public function destroy(int $id): JsonResponse
    {
        if (! DB::table('web_asesor')->where('id', $id)->delete()) {
            abort(404);
        }

        return response()->json(null, 204);
    }

    public function asignar(Request $request, int $conversacionId): JsonResponse
    {
        $asesorId = $request->validate(['asesor_id' => ['required', 'integer', 'exists:web_asesor,id']])['asesor_id'];

        DB::table('whatsapp_conversaciones')
            ->where('id', $conversacionId)
            ->update(['asesor_id' => $asesorId, 'updated_at' => now()->toDateTimeString()]);

        $asesor = DB::table('web_asesor')->where('id', $asesorId)->first();

        return response()->json($asesor);
    }
}
