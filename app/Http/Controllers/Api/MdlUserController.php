<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MdlUserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = $request->get('query', '');
        $size = (int) $request->get('pageSize', 50);
        $page = (int) $request->get('pageIndex', 1);

        $q = DB::table('mdl_user');
        if ($query) {
            $q->where(function ($q) use ($query) {
                $q->where('nombre', 'like', "%{$query}%")
                    ->orWhere('appaterno', 'like', "%{$query}%")
                    ->orWhere('ci', 'like', "%{$query}%");
            });
        }
        if ($request->has('id_us_reg')) {
            $q->where('id_us_reg', (int) $request->get('id_us_reg'));
        }
        if (! $request->boolean('conInactivos', false)) {
            $q->where('estado', 1);
        }

        $total = $q->count();
        $data = $q->orderBy('appaterno')->offset(($page - 1) * $size)->limit($size)->get();

        return response()->json(['data' => $data, 'total' => $total]);
    }

    public function show(int $id): JsonResponse
    {
        $row = DB::table('mdl_user')->where('id', $id)->first();
        if (! $row) {
            abort(404, 'Usuario Moodle no encontrado');
        }

        return response()->json($row);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id' => ['required', 'integer'],
            'id_us_reg' => ['nullable', 'integer'],
            'num_modusuario' => ['nullable', 'integer'],
            'nombre_usuario' => ['nullable', 'string', 'max:100'],
            'password' => ['nullable', 'string', 'max:200'],
            'nombre' => ['nullable', 'string', 'max:100'],
            'appaterno' => ['nullable', 'string', 'max:100'],
            'apmaterno' => ['nullable', 'string', 'max:200'],
            'ci' => ['nullable', 'string', 'max:200'],
            'expedido' => ['nullable', 'integer'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'celular' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:100'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'ciudad' => ['nullable', 'string', 'max:120'],
            'estado' => ['nullable', 'integer'],
            'per_modificar' => ['nullable', 'integer'],
        ]);
        $data['id_us_reg'] = $request->integer('id_us_reg', 0);
        $data['estado'] = $request->integer('estado', 1);
        $data['fecha_reg'] = now();

        DB::table('mdl_user')->insert($data);

        return response()->json(DB::table('mdl_user')->where('id', $data['id'])->first(), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = DB::table('mdl_user')->where('id', $id)->first();
        if (! $row) {
            abort(404, 'Usuario Moodle no encontrado');
        }

        $data = $request->validate([
            'nombre_usuario' => ['nullable', 'string', 'max:100'],
            'password' => ['nullable', 'string', 'max:200'],
            'nombre' => ['nullable', 'string', 'max:100'],
            'appaterno' => ['nullable', 'string', 'max:100'],
            'apmaterno' => ['nullable', 'string', 'max:200'],
            'ci' => ['nullable', 'string', 'max:200'],
            'expedido' => ['nullable', 'integer'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'celular' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:100'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'ciudad' => ['nullable', 'string', 'max:120'],
            'estado' => ['nullable', 'integer'],
            'per_modificar' => ['nullable', 'integer'],
        ]);
        DB::table('mdl_user')->where('id', $id)->update($data);

        return response()->json(DB::table('mdl_user')->where('id', $id)->first());
    }

    public function destroy(int $id): JsonResponse
    {
        DB::table('mdl_user')->where('id', $id)->update(['estado' => 0]);

        return response()->json(null, 204);
    }
}
