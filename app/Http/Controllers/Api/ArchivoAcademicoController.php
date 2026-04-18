<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ArchivoAcademicoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = $request->get('query', '');
        $size = (int) $request->get('pageSize', 50);
        $page = (int) $request->get('pageIndex', 1);

        $q = DB::table('t_archivo');
        if ($query) {
            $q->where('nombre', 'like', "%{$query}%");
        }
        if (! $request->boolean('conInactivos', false)) {
            $q->where('estado', 1);
        }

        $total = $q->count();
        $data = $q->orderBy('nombre')->offset(($page - 1) * $size)->limit($size)->get();

        return response()->json(['data' => $data, 'total' => $total]);
    }

    public function show(int $id): JsonResponse
    {
        $row = DB::table('t_archivo')->where('id_arch', $id)->first();
        if (! $row) {
            abort(404, 'Archivo no encontrado');
        }

        return response()->json($row);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_arch' => ['required', 'integer'],
            'id_us_reg' => ['nullable', 'integer'],
            'num_arch' => ['nullable', 'integer'],
            'nombre' => ['required', 'string', 'max:200'],
            'extensiones_permitidas' => ['nullable', 'string', 'max:300'],
            'peso_maximo' => ['nullable', 'integer'],
            'directorio_arch' => ['nullable', 'string', 'max:200'],
            'estado' => ['nullable', 'integer'],
        ]);
        $data['id_us_reg'] = $request->integer('id_us_reg', 0);
        $data['num_arch'] = $request->integer('num_arch', 0);
        $data['estado'] = $request->integer('estado', 1);
        $data['fecha_reg'] = now();

        DB::table('t_archivo')->insert($data);

        return response()->json(DB::table('t_archivo')->where('id_arch', $data['id_arch'])->first(), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = DB::table('t_archivo')->where('id_arch', $id)->first();
        if (! $row) {
            abort(404, 'Archivo no encontrado');
        }

        $data = $request->validate([
            'nombre' => ['sometimes', 'required', 'string', 'max:200'],
            'extensiones_permitidas' => ['nullable', 'string', 'max:300'],
            'peso_maximo' => ['nullable', 'integer'],
            'directorio_arch' => ['nullable', 'string', 'max:200'],
            'estado' => ['nullable', 'integer'],
        ]);
        DB::table('t_archivo')->where('id_arch', $id)->update($data);

        return response()->json(DB::table('t_archivo')->where('id_arch', $id)->first());
    }

    public function destroy(int $id): JsonResponse
    {
        DB::table('t_archivo')->where('id_arch', $id)->update(['estado' => 0]);

        return response()->json(null, 204);
    }
}
