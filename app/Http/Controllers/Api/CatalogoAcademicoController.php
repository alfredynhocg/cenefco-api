<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CatalogoAcademicoController extends Controller
{
    private const CATALOGOS = [
        'ciudades' => ['table' => 't_ciudad',              'pk' => 'id_ciudad',       'label' => 'nombre_ciudad'],
        'tipos-pago' => ['table' => 't_tipopago',            'pk' => 'id_tipopago',     'label' => 'titulo'],
        'tipos-universidad' => ['table' => 't_tipouniversidad',     'pk' => 'id_tipouniversidad', 'label' => 'nombre_tipouniversidad'],
        'universidades' => ['table' => 't_universidad',         'pk' => 'id_universidad',  'label' => 'nombre_universidad'],
        'ocupaciones' => ['table' => 't_ocupacion',           'pk' => 'id_ocupacion',    'label' => 'nombre_ocupacion'],
        'profesiones' => ['table' => 't_profesion',           'pk' => 'id_prof',         'label' => 'nombre_profesion'],
        'modalidades-titulacion' => ['table' => 't_modalidadtitulacion', 'pk' => 'id_modtit',      'label' => 'nombre_modalidad'],
        'categorias-plan' => ['table' => 't_catplan',             'pk' => 'id_catplan',      'label' => 'titulocat'],
        'niveles' => ['table' => 't_nivel',               'pk' => 'id_niv',          'label' => 'titulo'],
        'horas' => ['table' => 't_hora',                'pk' => 'id_hor',          'label' => 'titulo'],
        'menciones' => ['table' => 't_mension',             'pk' => 'id_mension',      'label' => 'titulo_mension'],
        'meses-pago' => ['table' => 't_mespago',             'pk' => 'id_mespago',      'label' => 'mes'],
        'tipos-programa' => ['table' => 't_tipoprograma',        'pk' => 'id_tipoprograma', 'label' => 'nombre_tipoprograma'],
        'planes-doc' => ['table' => 't_plandoc',             'pk' => 'id_plandoc',      'label' => 'titulo_plandoc'],
        'grupos-documento' => ['table' => 't_grupodocumento',      'pk' => 'id_grupodocumento', 'label' => 'nombre_grupodocumento'],
        'grupos-permiso' => ['table' => 't_grupopermiso',        'pk' => 'id_grupopermiso', 'label' => 'nombre_grupopermiso'],
        'categorias-articulo' => ['table' => 't_categoria_articulo',  'pk' => 'id_cat_art',      'label' => 'nombre_cat'],
    ];

    private function resolveCatalogo(string $catalogo): array
    {
        if (! array_key_exists($catalogo, self::CATALOGOS)) {
            abort(404, "Catálogo '{$catalogo}' no existe");
        }

        return self::CATALOGOS[$catalogo];
    }

    public function index(Request $request, string $catalogo): JsonResponse
    {
        $cfg = $this->resolveCatalogo($catalogo);
        $query = $request->get('query', '');
        $size = (int) $request->get('pageSize', 100);
        $page = (int) $request->get('pageIndex', 1);

        $q = DB::table($cfg['table']);
        if ($query) {
            $q->where($cfg['label'], 'like', "%{$query}%");
        }
        if (! $request->boolean('conInactivos', false)) {
            $q->where('estado', 1);
        }

        $total = $q->count();
        $data = $q->orderBy($cfg['label'])->offset(($page - 1) * $size)->limit($size)->get();

        return response()->json(['data' => $data, 'total' => $total]);
    }

    public function show(Request $request, string $catalogo, int $id): JsonResponse
    {
        $cfg = $this->resolveCatalogo($catalogo);
        $row = DB::table($cfg['table'])->where($cfg['pk'], $id)->first();
        if (! $row) {
            abort(404, 'Registro no encontrado');
        }

        return response()->json($row);
    }

    public function store(Request $request, string $catalogo): JsonResponse
    {
        $cfg = $this->resolveCatalogo($catalogo);
        $pk = $cfg['pk'];
        $lbl = $cfg['label'];

        $data = $request->validate([
            $pk => ['required', 'integer'],
            'id_us_reg' => ['nullable', 'integer'],
            $lbl => ['required', 'string', 'max:200'],
            'estado' => ['nullable', 'integer'],
        ]);
        $data['id_us_reg'] = $request->integer('id_us_reg', 0);
        $data['estado'] = $request->integer('estado', 1);
        $data['fecha_reg'] = now();

        DB::table($cfg['table'])->insert($data);
        $row = DB::table($cfg['table'])->where($pk, $data[$pk])->first();

        return response()->json($row, 201);
    }

    public function update(Request $request, string $catalogo, int $id): JsonResponse
    {
        $cfg = $this->resolveCatalogo($catalogo);
        $pk = $cfg['pk'];
        $lbl = $cfg['label'];

        $row = DB::table($cfg['table'])->where($pk, $id)->first();
        if (! $row) {
            abort(404, 'Registro no encontrado');
        }

        $data = $request->validate([
            $lbl => ['sometimes', 'required', 'string', 'max:200'],
            'estado' => ['nullable', 'integer'],
        ]);
        DB::table($cfg['table'])->where($pk, $id)->update($data);

        return response()->json(DB::table($cfg['table'])->where($pk, $id)->first());
    }

    public function destroy(Request $request, string $catalogo, int $id): JsonResponse
    {
        $cfg = $this->resolveCatalogo($catalogo);
        DB::table($cfg['table'])->where($cfg['pk'], $id)->update(['estado' => 0]);

        return response()->json(null, 204);
    }
}
