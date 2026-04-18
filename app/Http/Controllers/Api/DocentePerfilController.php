<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DocentePerfilController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = $request->get('query', '');
        $size = (int) $request->get('pageSize', 20);
        $page = (int) $request->get('pageIndex', 1);

        $q = DB::table('web_docente_perfil');
        if ($query) {
            $q->where(function ($sq) use ($query) {
                $sq->where('nombre_completo', 'like', "%{$query}%")
                    ->orWhere('especialidad', 'like', "%{$query}%");
            });
        }
        if ($request->has('tipo')) {
            $q->where('tipo', $request->get('tipo'));
        }
        if ($request->has('estado')) {
            $q->where('estado', $request->get('estado'));
        }
        if ($request->boolean('soloVisibles', false)) {
            $q->where('mostrar_en_web', true);
        }

        $total = $q->count();
        $data = $q->orderBy('orden')->orderBy('nombre_completo')->offset(($page - 1) * $size)->limit($size)->get();

        return response()->json(['data' => $data, 'total' => $total]);
    }

    public function show(int $id): JsonResponse
    {
        $row = DB::table('web_docente_perfil')->find($id);
        if (! $row) {
            abort(404, 'Docente no encontrado');
        }

        return response()->json($row);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'usuario_id' => ['nullable', 'integer'],
            'nombre_completo' => ['required', 'string', 'max:300'],
            'titulo_academico' => ['nullable', 'string', 'max:200'],
            'especialidad' => ['nullable', 'string', 'max:300'],
            'biografia' => ['nullable', 'string'],
            'foto_url' => ['nullable', 'string', 'max:255'],
            'foto_alt' => ['nullable', 'string', 'max:255'],
            'email_publico' => ['nullable', 'email', 'max:100'],
            'linkedin_url' => ['nullable', 'string', 'max:255'],
            'twitter_url' => ['nullable', 'string', 'max:255'],
            'sitio_web_url' => ['nullable', 'string', 'max:255'],
            'tipo' => ['nullable', 'string', 'max:100'],
            'mostrar_en_web' => ['nullable', 'boolean'],
            'orden' => ['nullable', 'integer'],
            'estado' => ['nullable', 'string', 'max:50'],
        ]);
        $data['tipo'] = $data['tipo'] ?? 'docente';
        $data['mostrar_en_web'] = $request->boolean('mostrar_en_web', true);
        $data['orden'] = $request->integer('orden', 0);
        $data['estado'] = $data['estado'] ?? 'publicado';
        $data['created_at'] = now();

        $id = DB::table('web_docente_perfil')->insertGetId($data);
        $row = DB::table('web_docente_perfil')->find($id);

        return response()->json($row, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = DB::table('web_docente_perfil')->find($id);
        if (! $row) {
            abort(404, 'Docente no encontrado');
        }

        $data = $request->validate([
            'usuario_id' => ['nullable', 'integer'],
            'nombre_completo' => ['sometimes', 'required', 'string', 'max:300'],
            'titulo_academico' => ['nullable', 'string', 'max:200'],
            'especialidad' => ['nullable', 'string', 'max:300'],
            'biografia' => ['nullable', 'string'],
            'foto_url' => ['nullable', 'string', 'max:255'],
            'foto_alt' => ['nullable', 'string', 'max:255'],
            'email_publico' => ['nullable', 'email', 'max:100'],
            'linkedin_url' => ['nullable', 'string', 'max:255'],
            'twitter_url' => ['nullable', 'string', 'max:255'],
            'sitio_web_url' => ['nullable', 'string', 'max:255'],
            'tipo' => ['nullable', 'string', 'max:100'],
            'mostrar_en_web' => ['nullable', 'boolean'],
            'orden' => ['nullable', 'integer'],
            'estado' => ['nullable', 'string', 'max:50'],
        ]);
        $data['updated_at'] = now();
        DB::table('web_docente_perfil')->where('id', $id)->update($data);

        return response()->json(DB::table('web_docente_perfil')->find($id));
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = DB::table('web_docente_perfil')->where('id', $id)->delete();
        if (! $deleted) {
            abort(404, 'Docente no encontrado');
        }

        return response()->json(null, 204);
    }
}
