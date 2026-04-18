<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InscripcionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $size = (int) $request->get('pageSize', 30);
        $page = (int) $request->get('pageIndex', 1);

        $q = DB::table('t_inscripcion');
        if ($request->has('id_us')) {
            $q->where('id_us', (int) $request->get('id_us'));
        }
        if ($request->has('id_imp')) {
            $q->where('id_imp', (int) $request->get('id_imp'));
        }
        if ($request->has('periodo')) {
            $q->where('periodo', $request->get('periodo'));
        }
        if ($request->has('gestion')) {
            $q->where('gestion', $request->get('gestion'));
        }
        if (! $request->boolean('conInactivos', false)) {
            $q->where('estado', 1);
        }

        $total = $q->count();
        $data = $q->orderByDesc('fecha_ins')->offset(($page - 1) * $size)->limit($size)->get();

        return response()->json(['data' => $data, 'total' => $total]);
    }

    public function show(int $id): JsonResponse
    {
        $row = DB::table('t_inscripcion')->where('id_ins', $id)->first();
        if (! $row) {
            abort(404, 'Inscripción no encontrada');
        }

        return response()->json($row);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_ins' => ['required', 'integer'],
            'id_us_reg' => ['nullable', 'integer'],
            'fecha_ins' => ['nullable', 'date'],
            'id_us' => ['required', 'integer'],
            'id_imp' => ['required', 'integer'],
            'observacion_ins' => ['nullable', 'string'],
            'observacion' => ['nullable', 'string'],
            'periodo' => ['nullable', 'string', 'max:30'],
            'gestion' => ['nullable', 'string', 'max:10'],
            'estado' => ['nullable', 'integer'],
        ]);
        $data['id_us_reg'] = $request->integer('id_us_reg', 0);
        $data['estado'] = $request->integer('estado', 1);
        $data['fecha_reg'] = now();

        DB::table('t_inscripcion')->insert($data);

        return response()->json(DB::table('t_inscripcion')->where('id_ins', $data['id_ins'])->first(), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = DB::table('t_inscripcion')->where('id_ins', $id)->first();
        if (! $row) {
            abort(404, 'Inscripción no encontrada');
        }

        $data = $request->validate([
            'fecha_ins' => ['nullable', 'date'],
            'observacion_ins' => ['nullable', 'string'],
            'observacion' => ['nullable', 'string'],
            'estado' => ['nullable', 'integer'],
        ]);
        DB::table('t_inscripcion')->where('id_ins', $id)->update($data);

        return response()->json(DB::table('t_inscripcion')->where('id_ins', $id)->first());
    }

    public function destroy(int $id): JsonResponse
    {
        DB::table('t_inscripcion')->where('id_ins', $id)->update(['estado' => 0]);

        return response()->json(null, 204);
    }
}
