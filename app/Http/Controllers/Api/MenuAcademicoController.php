<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MenuAcademicoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = $request->get('query', '');
        $size = (int) $request->get('pageSize', 100);
        $page = (int) $request->get('pageIndex', 1);

        $q = DB::table('t_menu');
        if ($query) {
            $q->where('nombre_cat', 'like', "%{$query}%");
        }
        if ($request->has('id_mod')) {
            $q->where('id_mod', (int) $request->get('id_mod'));
        }
        if (! $request->boolean('conInactivos', false)) {
            $q->where('estado', 1);
        }

        $total = $q->count();
        $data = $q->orderBy('nombre_cat')->offset(($page - 1) * $size)->limit($size)->get();

        return response()->json(['data' => $data, 'total' => $total]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $row = DB::table('t_menu')->where('id_men', $id)->first();
        if (! $row) {
            abort(404, 'Menú no encontrado');
        }

        return response()->json($row);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_men' => ['required', 'numeric'],
            'id_mod' => ['required', 'integer'],
            'id_us_reg' => ['nullable', 'integer'],
            'num_cat' => ['nullable', 'integer'],
            'nombre_cat' => ['required', 'string', 'max:200'],
            'sub_cat' => ['nullable', 'string', 'max:200'],
            'id_niv' => ['nullable', 'string', 'max:200'],
            'id_niv_exm' => ['nullable', 'string'],
            'acceso' => ['nullable', 'string', 'max:200'],
            'icono' => ['nullable', 'string', 'max:200'],
            'ancla' => ['nullable', 'string', 'max:200'],
            'reglavista_clase' => ['nullable', 'string', 'max:200'],
            'xgrupos' => ['nullable', 'string'],
            'enlace' => ['nullable', 'string', 'max:200'],
            'estado' => ['nullable', 'integer'],
        ]);
        $data['id_us_reg'] = $request->integer('id_us_reg', 0);
        $data['num_cat'] = $request->integer('num_cat', 0);
        $data['estado'] = $request->integer('estado', 1);
        $data['fecha_reg'] = now();

        DB::table('t_menu')->insert($data);

        return response()->json(DB::table('t_menu')->where('id_men', $data['id_men'])->first(), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = DB::table('t_menu')->where('id_men', $id)->first();
        if (! $row) {
            abort(404, 'Menú no encontrado');
        }

        $data = $request->validate([
            'nombre_cat' => ['sometimes', 'required', 'string', 'max:200'],
            'sub_cat' => ['nullable', 'string', 'max:200'],
            'id_niv' => ['nullable', 'string', 'max:200'],
            'id_niv_exm' => ['nullable', 'string'],
            'acceso' => ['nullable', 'string', 'max:200'],
            'icono' => ['nullable', 'string', 'max:200'],
            'ancla' => ['nullable', 'string', 'max:200'],
            'reglavista_clase' => ['nullable', 'string', 'max:200'],
            'xgrupos' => ['nullable', 'string'],
            'enlace' => ['nullable', 'string', 'max:200'],
            'estado' => ['nullable', 'integer'],
        ]);
        DB::table('t_menu')->where('id_men', $id)->update($data);

        return response()->json(DB::table('t_menu')->where('id_men', $id)->first());
    }

    public function destroy(int $id): JsonResponse
    {
        DB::table('t_menu')->where('id_men', $id)->update(['estado' => 0]);

        return response()->json(null, 204);
    }
}
