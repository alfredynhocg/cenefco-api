<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CursoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = DB::table('t_programa as p')
            ->leftJoin('web_categoria_programa as cat', 'p.categoria_web_id', '=', 'cat.id')
            ->leftJoin('t_tipoprograma as tip', function ($join) {
                $join->on('p.id_tipoprograma', '=', 'tip.id_tipoprograma')
                    ->on('p.id_us_reg', '=', 'tip.id_us_reg');
            })
            ->select(
                'p.id_programa',
                'p.id_us_reg',
                'p.nombre_programa',
                'p.slug',
                'p.descripcion',
                'p.objetivo',
                'p.dirigido',
                'p.requisitos',
                'p.inversion',
                'p.creditaje',
                'p.nota',
                'p.url_video',
                'p.foto',
                'p.imagen_banner_url',
                'p.imagen_alt',
                'p.inicio_actividades',
                'p.finalizacion_actividades',
                'p.inicio_inscripciones',
                'p.id_tipoprograma',
                'tip.nombre_tipoprograma as tipo_nombre',
                'p.categoria_web_id',
                'cat.nombre as categoria_nombre',
                'p.estado',
                'p.estado_web',
                'p.destacado',
                'p.orden',
                'p.meta_titulo',
                'p.meta_descripcion',
                'p.fecha_publicacion',
                'p.fecha_reg',
            );

        if ($search = $request->get('query')) {
            $q->where(function ($sub) use ($search) {
                $sub->where('p.nombre_programa', 'like', "%{$search}%")
                    ->orWhere('p.descripcion', 'like', "%{$search}%");
            });
        }

        $pageSize = (int) $request->get('pageSize', 15);
        $pageIndex = (int) $request->get('pageIndex', 1);
        $offset = ($pageIndex - 1) * $pageSize;

        $total = $q->count();
        $items = (clone $q)
            ->orderBy('p.orden', 'asc')
            ->orderBy('p.id_programa', 'desc')
            ->offset($offset)
            ->limit($pageSize)
            ->get();

        return response()->json(['data' => $items, 'total' => $total]);
    }

    public function show(int $id): JsonResponse
    {
        $curso = DB::table('t_programa as p')
            ->leftJoin('web_categoria_programa as cat', 'p.categoria_web_id', '=', 'cat.id')
            ->leftJoin('t_tipoprograma as tip', function ($join) {
                $join->on('p.id_tipoprograma', '=', 'tip.id_tipoprograma')
                    ->on('p.id_us_reg', '=', 'tip.id_us_reg');
            })
            ->select(
                'p.*',
                'tip.nombre_tipoprograma as tipo_nombre',
                'cat.nombre as categoria_nombre',
            )
            ->where('p.id_programa', $id)
            ->first();

        if (! $curso) {
            abort(404, 'Curso no encontrado');
        }

        return response()->json($curso);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre_programa' => ['required', 'string', 'max:200'],
            'slug' => ['nullable', 'string', 'max:300'],
            'descripcion' => ['nullable', 'string'],
            'objetivo' => ['nullable', 'string'],
            'dirigido' => ['nullable', 'string'],
            'requisitos' => ['nullable', 'string'],
            'inversion' => ['nullable', 'string'],
            'creditaje' => ['nullable', 'string'],
            'nota' => ['nullable', 'string'],
            'foto' => ['nullable', 'string', 'max:500'],
            'titulo_documento1' => ['nullable', 'string', 'max:200'],
            'documento1' => ['nullable', 'string', 'max:500'],
            'imagen_banner_url' => ['nullable', 'string', 'max:500'],
            'imagen_alt' => ['nullable', 'string', 'max:300'],
            'url_video' => ['nullable', 'string', 'max:500'],
            'url_whatsapp' => ['nullable', 'string', 'max:500'],
            'inicio_actividades' => ['nullable', 'date'],
            'finalizacion_actividades' => ['nullable', 'date'],
            'inicio_inscripciones' => ['nullable', 'date'],
            'id_tipoprograma' => ['nullable', 'integer'],
            'categoria_web_id' => ['nullable', 'integer'],
            'estado_web' => ['nullable', 'in:borrador,publicado'],
            'destacado' => ['nullable', 'boolean'],
            'orden' => ['nullable', 'integer'],
            'meta_titulo' => ['nullable', 'string', 'max:300'],
            'meta_descripcion' => ['nullable', 'string', 'max:500'],
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['nombre_programa']);
        }

        $nextId = (DB::table('t_programa')->max('id_programa') ?? 0) + 1;

        $data['id_programa'] = $nextId;
        $data['id_us_reg'] = 0;
        $data['estado'] = 1;
        $data['estado_web'] = $data['estado_web'] ?? 'borrador';
        $data['destacado'] = $data['destacado'] ?? false;
        $data['orden'] = $data['orden'] ?? 0;
        $data['fecha_reg'] = now()->toDateTimeString();
        $data['updated_at'] = now()->toDateTimeString();

        DB::table('t_programa')->insert($data);

        return response()->json(
            DB::table('t_programa')->where('id_programa', $nextId)->first(),
            201
        );
    }

    public function update(Request $request, int $id): JsonResponse
    {
        if (! DB::table('t_programa')->where('id_programa', $id)->exists()) {
            abort(404, 'Curso no encontrado');
        }

        $data = $request->validate([
            'nombre_programa' => ['sometimes', 'required', 'string', 'max:200'],
            'slug' => ['nullable', 'string', 'max:300'],
            'descripcion' => ['nullable', 'string'],
            'objetivo' => ['nullable', 'string'],
            'dirigido' => ['nullable', 'string'],
            'requisitos' => ['nullable', 'string'],
            'inversion' => ['nullable', 'string'],
            'creditaje' => ['nullable', 'string'],
            'nota' => ['nullable', 'string'],
            'foto' => ['nullable', 'string', 'max:500'],
            'titulo_documento1' => ['nullable', 'string', 'max:200'],
            'documento1' => ['nullable', 'string', 'max:500'],
            'imagen_banner_url' => ['nullable', 'string', 'max:500'],
            'imagen_alt' => ['nullable', 'string', 'max:300'],
            'url_video' => ['nullable', 'string', 'max:500'],
            'url_whatsapp' => ['nullable', 'string', 'max:500'],
            'inicio_actividades' => ['nullable', 'date'],
            'finalizacion_actividades' => ['nullable', 'date'],
            'inicio_inscripciones' => ['nullable', 'date'],
            'id_tipoprograma' => ['nullable', 'integer'],
            'categoria_web_id' => ['nullable', 'integer'],
            'estado_web' => ['nullable', 'in:borrador,publicado'],
            'destacado' => ['nullable', 'boolean'],
            'orden' => ['nullable', 'integer'],
            'meta_titulo' => ['nullable', 'string', 'max:300'],
            'meta_descripcion' => ['nullable', 'string', 'max:500'],
        ]);

        $data['updated_at'] = now()->toDateTimeString();

        DB::table('t_programa')->where('id_programa', $id)->update($data);

        return response()->json(
            DB::table('t_programa')->where('id_programa', $id)->first()
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = DB::table('t_programa')->where('id_programa', $id)->delete();

        if (! $deleted) {
            abort(404, 'Curso no encontrado');
        }

        return response()->json(null, 204);
    }
}
