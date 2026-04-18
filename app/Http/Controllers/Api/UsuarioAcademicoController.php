<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UsuarioAcademicoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = $request->get('query', '');
        $size = (int) $request->get('pageSize', 30);
        $page = (int) $request->get('pageIndex', 1);

        $q = DB::table('t_usuario')->select([
            'id_us', 'tipoestudiante', 'nombre_usuario', 'categoria',
            'titulo_academico', 'appaterno', 'apmaterno', 'nombre',
            'ci', 'expedido', 'telefono', 'celular', 'genero',
            'email', 'ciudad', 'pais', 'id_universidad', 'id_carrera',
            'estado', 'fecha_reg',
        ]);
        if ($query) {
            $q->where(function ($sq) use ($query) {
                $sq->where('nombre', 'like', "%{$query}%")
                    ->orWhere('appaterno', 'like', "%{$query}%")
                    ->orWhere('ci', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%");
            });
        }
        if ($request->has('tipoestudiante')) {
            $q->where('tipoestudiante', $request->get('tipoestudiante'));
        }
        if (! $request->boolean('conInactivos', false)) {
            $q->where('estado', 1);
        }

        $total = $q->count();
        $data = $q->orderBy('appaterno')->orderBy('nombre')
            ->offset(($page - 1) * $size)->limit($size)->get();

        return response()->json(['data' => $data, 'total' => $total]);
    }

    public function show(int $id): JsonResponse
    {
        $row = DB::table('t_usuario')
            ->select(['id_us', 'tipoestudiante', 'nombre_usuario', 'categoria',
                'titulo_academico', 'titulo_academico2', 'appaterno', 'apmaterno', 'nombre',
                'ci', 'expedido', 'telefono', 'celular', 'genero', 'email',
                'direccion', 'ciudad', 'estado_civil', 'pais',
                'id_universidad', 'id_carrera', 'estado', 'fecha_reg'])
            ->where('id_us', $id)->first();
        if (! $row) {
            abort(404, 'Usuario no encontrado');
        }

        return response()->json($row);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_us' => ['required', 'integer'],
            'id_us_reg' => ['nullable', 'integer'],
            'tipoestudiante' => ['nullable', 'string', 'max:200'],
            'nombre_usuario' => ['nullable', 'string', 'max:100'],
            'categoria' => ['nullable', 'string', 'max:200'],
            'titulo_academico' => ['nullable', 'string', 'max:200'],
            'appaterno' => ['nullable', 'string', 'max:100'],
            'apmaterno' => ['nullable', 'string', 'max:200'],
            'nombre' => ['required', 'string', 'max:100'],
            'ci' => ['nullable', 'string', 'max:200'],
            'expedido' => ['nullable', 'integer'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'celular' => ['nullable', 'string', 'max:20'],
            'genero' => ['nullable', 'integer'],
            'email' => ['nullable', 'email', 'max:100'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'ciudad' => ['nullable', 'string', 'max:120'],
            'pais' => ['nullable', 'string', 'max:200'],
            'id_universidad' => ['nullable', 'integer'],
            'id_carrera' => ['nullable', 'integer'],
            'estado' => ['nullable', 'integer'],
        ]);
        $data['id_us_reg'] = $request->integer('id_us_reg', 0);
        $data['estado'] = $request->integer('estado', 1);
        $data['fecha_reg'] = now();

        DB::table('t_usuario')->insert($data);

        return response()->json(DB::table('t_usuario')->where('id_us', $data['id_us'])->first(), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = DB::table('t_usuario')->where('id_us', $id)->first();
        if (! $row) {
            abort(404, 'Usuario no encontrado');
        }

        $data = $request->validate([
            'tipoestudiante' => ['nullable', 'string', 'max:200'],
            'nombre_usuario' => ['nullable', 'string', 'max:100'],
            'categoria' => ['nullable', 'string', 'max:200'],
            'titulo_academico' => ['nullable', 'string', 'max:200'],
            'appaterno' => ['nullable', 'string', 'max:100'],
            'apmaterno' => ['nullable', 'string', 'max:200'],
            'nombre' => ['sometimes', 'required', 'string', 'max:100'],
            'ci' => ['nullable', 'string', 'max:200'],
            'expedido' => ['nullable', 'integer'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'celular' => ['nullable', 'string', 'max:20'],
            'genero' => ['nullable', 'integer'],
            'email' => ['nullable', 'email', 'max:100'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'ciudad' => ['nullable', 'string', 'max:120'],
            'pais' => ['nullable', 'string', 'max:200'],
            'id_universidad' => ['nullable', 'integer'],
            'id_carrera' => ['nullable', 'integer'],
            'estado' => ['nullable', 'integer'],
        ]);
        DB::table('t_usuario')->where('id_us', $id)->update($data);

        return response()->json(DB::table('t_usuario')->where('id_us', $id)->first());
    }

    public function destroy(int $id): JsonResponse
    {
        DB::table('t_usuario')->where('id_us', $id)->update(['estado' => 0]);

        return response()->json(null, 204);
    }
}
