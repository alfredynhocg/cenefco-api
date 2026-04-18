<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlanAcademicoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = $request->get('query', '');
        $size = (int) $request->get('pageSize', 30);
        $page = (int) $request->get('pageIndex', 1);

        $q = DB::table('t_plan');
        if ($query) {
            $q->where(function ($sq) use ($query) {
                $sq->where('titulo', 'like', "%{$query}%")
                    ->orWhere('titulo_plan', 'like', "%{$query}%");
            });
        }
        if ($request->has('id_catplan')) {
            $q->where('id_catplan', (int) $request->get('id_catplan'));
        }
        if (! $request->boolean('conInactivos', false)) {
            $q->where('estado', 1);
        }

        $total = $q->count();
        $data = $q->orderBy('titulo')->offset(($page - 1) * $size)->limit($size)->get();

        return response()->json(['data' => $data, 'total' => $total]);
    }

    public function show(int $id): JsonResponse
    {
        $row = DB::table('t_plan')->where('id_plan', $id)->first();
        if (! $row) {
            abort(404, 'Plan no encontrado');
        }

        return response()->json($row);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_plan' => ['required', 'integer'],
            'id_us_reg' => ['nullable', 'integer'],
            'titulo' => ['required', 'string', 'max:200'],
            'titulo_plan' => ['nullable', 'string', 'max:200'],
            'convenio' => ['nullable', 'string', 'max:200'],
            'anio' => ['nullable', 'string', 'max:200'],
            'numero_resolucion' => ['nullable', 'string', 'max:200'],
            'costo' => ['nullable', 'string', 'max:200'],
            'nro_cuotas' => ['nullable', 'string', 'max:200'],
            'descuento' => ['nullable', 'string', 'max:200'],
            'costo_por_cuota' => ['nullable', 'string', 'max:200'],
            'id_catplan' => ['nullable', 'integer'],
            'estado' => ['nullable', 'integer'],
        ]);
        $data['id_us_reg'] = $request->integer('id_us_reg', 0);
        $data['estado'] = $request->integer('estado', 1);
        $data['fecha_reg'] = now();

        DB::table('t_plan')->insert($data);

        return response()->json(DB::table('t_plan')->where('id_plan', $data['id_plan'])->first(), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = DB::table('t_plan')->where('id_plan', $id)->first();
        if (! $row) {
            abort(404, 'Plan no encontrado');
        }

        $data = $request->validate([
            'titulo' => ['sometimes', 'required', 'string', 'max:200'],
            'titulo_plan' => ['nullable', 'string', 'max:200'],
            'convenio' => ['nullable', 'string', 'max:200'],
            'anio' => ['nullable', 'string', 'max:200'],
            'numero_resolucion' => ['nullable', 'string', 'max:200'],
            'costo' => ['nullable', 'string', 'max:200'],
            'nro_cuotas' => ['nullable', 'string', 'max:200'],
            'descuento' => ['nullable', 'string', 'max:200'],
            'costo_por_cuota' => ['nullable', 'string', 'max:200'],
            'id_catplan' => ['nullable', 'integer'],
            'estado' => ['nullable', 'integer'],
        ]);
        DB::table('t_plan')->where('id_plan', $id)->update($data);

        return response()->json(DB::table('t_plan')->where('id_plan', $id)->first());
    }

    public function destroy(int $id): JsonResponse
    {
        DB::table('t_plan')->where('id_plan', $id)->update(['estado' => 0]);

        return response()->json(null, 204);
    }
}
