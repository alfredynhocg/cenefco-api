<?php

namespace App\Http\Controllers\Api;

use App\Application\Moodle\Commands\CreateMoodleCourseCommand;
use App\Application\Moodle\Handlers\CreateMoodleCourseHandler;
use App\Application\Moodle\QueryHandlers\GetMoodleCoursesQueryHandler;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MoodleCourseController extends Controller
{
    public function __construct(
        private readonly CreateMoodleCourseHandler $createHandler,
        private readonly GetMoodleCoursesQueryHandler $getCoursesHandler,
    ) {}

    public function index(): JsonResponse
    {
        return response()->json($this->getCoursesHandler->handle());
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'fullname' => 'required|string|max:255',
            'shortname' => 'required|string|max:100',
            'categoryid' => 'sometimes|integer',
            'summary' => 'sometimes|string',
        ]);

        $dto = $this->createHandler->handle(new CreateMoodleCourseCommand(
            fullname: $request->fullname,
            shortname: $request->shortname,
            categoryid: $request->integer('categoryid', 1),
            summary: $request->string('summary', '')->toString(),
        ));

        return response()->json($dto, 201);
    }

    public function fromCurso(int $id): JsonResponse
    {
        $curso = DB::table('t_programa')->where('id_programa', $id)->first();

        if (! $curso) {
            return response()->json(['message' => 'Curso no encontrado'], 404);
        }

        $shortname = $curso->slug
            ? Str::upper(Str::limit($curso->slug, 30, ''))
            : 'CURSO-'.$id;

        $summary = implode("\n\n", array_filter([
            strip_tags($curso->descripcion ?? ''),
            $curso->objetivo ? 'Objetivo: '.strip_tags($curso->objetivo) : null,
            $curso->dirigido ? 'Dirigido a: '.strip_tags($curso->dirigido) : null,
            $curso->requisitos ? 'Requisitos: '.strip_tags($curso->requisitos) : null,
        ]));

        $startdate = $curso->inicio_actividades
            ? strtotime($curso->inicio_actividades)
            : 0;

        $enddate = $curso->finalizacion_actividades
            ? strtotime($curso->finalizacion_actividades)
            : 0;

        $imageUrl = null;
        if (! empty($curso->foto)) {
            $foto = ltrim($curso->foto, '/');
            $imageUrl = rtrim(config('app.url'), '/').'/'.$foto;
        }

        logger()->debug('MoodleCurso imagen', ['foto' => $curso->foto, 'imageUrl' => $imageUrl]);

        $dto = $this->createHandler->handle(new CreateMoodleCourseCommand(
            fullname: $curso->nombre_programa,
            shortname: $shortname,
            categoryid: 1,
            summary: $summary,
            startdate: $startdate,
            enddate: $enddate,
            imageUrl: $imageUrl,
        ));

        return response()->json($dto, 201);
    }
}
