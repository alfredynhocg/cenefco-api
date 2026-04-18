<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SuscriptorController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = $request->get('query', '');
        $size = (int) $request->get('pageSize', 20);
        $page = (int) $request->get('pageIndex', 1);

        $q = DB::table('web_suscriptor');
        if ($query) {
            $q->where(function ($sq) use ($query) {
                $sq->where('email', 'like', "%{$query}%")
                    ->orWhere('nombre', 'like', "%{$query}%");
            });
        }
        if ($request->has('confirmado')) {
            $q->where('confirmado', $request->boolean('confirmado'));
        }
        if ($request->has('activo')) {
            $q->where('activo', $request->boolean('activo'));
        }

        $total = $q->count();
        $data = $q->orderByDesc('id')->offset(($page - 1) * $size)->limit($size)->get();

        return response()->json(['data' => $data, 'total' => $total]);
    }

    public function show(int $id): JsonResponse
    {
        $row = DB::table('web_suscriptor')->find($id);
        if (! $row) {
            abort(404, 'Suscriptor no encontrado');
        }

        return response()->json($row);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:100', 'unique:web_suscriptor,email'],
            'nombre' => ['nullable', 'string', 'max:200'],
            'origen' => ['nullable', 'string', 'max:100'],
        ]);
        $data['activo'] = true;
        $data['confirmado'] = false;
        $data['created_at'] = now();

        $id = DB::table('web_suscriptor')->insertGetId($data);
        $row = DB::table('web_suscriptor')->find($id);

        return response()->json($row, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = DB::table('web_suscriptor')->find($id);
        if (! $row) {
            abort(404, 'Suscriptor no encontrado');
        }

        $data = $request->validate([
            'nombre' => ['nullable', 'string', 'max:200'],
            'confirmado' => ['nullable', 'boolean'],
            'activo' => ['nullable', 'boolean'],
        ]);
        $data['updated_at'] = now();
        DB::table('web_suscriptor')->where('id', $id)->update($data);

        return response()->json(DB::table('web_suscriptor')->find($id));
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = DB::table('web_suscriptor')->where('id', $id)->delete();
        if (! $deleted) {
            abort(404, 'Suscriptor no encontrado');
        }

        return response()->json(null, 204);
    }
}
