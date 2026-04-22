# CLAUDE.md — Guía para Agentes AI y Onboarding Técnico

> Este archivo es la referencia principal para Claude Code y cualquier desarrollador nuevo.
> Describe los patrones del proyecto, convenciones estrictas y ejemplos reales de código.

---

## Qué es este proyecto

**cenefco API** es una API REST construida con Laravel 12 para la gestión de cursos, diplomados y programas de formación continua. Centraliza la administración de estudiantes, inscripciones, notas, pagos, docentes y la estructura académica de los programas.
Implementa **DDD (Domain-Driven Design) + CQRS** de forma estricta en todas las capas.

Stack: PHP 8.2 · Laravel 12 · MySQL 8 · Laravel Sanctum · DomPDF · Spatie Laravel Settings

Módulos principales: Cursos · Diplomados · Programas · Inscripciones · Notas · Pagos · Docentes · Estudiantes · Horarios · Usuarios y roles · Permisos · Contenido web · **Web Institucional** · **Certificados con QR** · **Pre-inscripción** · **WhatsApp grupos**

---

## Estructura de carpetas

```text
app/
├── Domain/                  # Contratos (interfaces), excepciones, enums — SIN dependencias externas
│   ├── Noticias/
│   │   ├── Contracts/
│   │   │   └── NoticiaRepositoryInterface.php
│   │   └── Exceptions/
│   │       └── NoticiaNotFoundException.php
│   └── ...
│
├── Application/             # Commands, Queries, Handlers, DTOs — orquesta el dominio
│   ├── Noticias/
│   │   ├── Commands/
│   │   │   ├── CreateNoticiaCommand.php
│   │   │   ├── UpdateNoticiaCommand.php
│   │   │   └── DeleteNoticiaCommand.php
│   │   ├── Handlers/
│   │   │   ├── CreateNoticiaHandler.php
│   │   │   ├── UpdateNoticiaHandler.php
│   │   │   └── DeleteNoticiaHandler.php
│   │   ├── Queries/
│   │   │   ├── GetNoticiasQuery.php
│   │   │   └── GetNoticiaBySlugQuery.php
│   │   ├── QueryHandlers/
│   │   │   ├── GetNoticiasQueryHandler.php
│   │   │   └── GetNoticiaBySlugQueryHandler.php
│   │   └── DTOs/
│   │       └── NoticiaDTO.php
│   └── ...
│
├── Infrastructure/          # Implementaciones concretas: Eloquent, APIs externas
│   ├── Noticias/
│   │   ├── Models/
│   │   │   └── Noticia.php
│   │   └── Repositories/
│   │       └── EloquentNoticiaRepository.php
│   └── ...
│
├── Http/                    # Controllers, Requests, Middleware — solo entrada/salida HTTP
│   ├── Controllers/Api/
│   │   └── NoticiaController.php
│   ├── Requests/Noticias/
│   │   ├── StoreNoticiaRequest.php
│   │   └── UpdateNoticiaRequest.php
│   └── Middleware/
│
└── Providers/
    └── DomainServiceProvider.php   # Bindea interfaces → implementaciones
```

---

## Reglas estrictas de arquitectura

### Lo que ESTÁ PROHIBIDO

```php
// ❌ NUNCA: Eloquent en la capa Domain
namespace App\Domain\Noticias\Contracts;
use App\Infrastructure\Noticias\Models\Noticia; // ← PROHIBIDO

// ❌ NUNCA: Lógica de negocio en Controllers
public function store(Request $request): JsonResponse {
    $noticia = Noticia::create($request->all()); // ← PROHIBIDO
    return response()->json($noticia);
}

// ❌ NUNCA: DB::raw o consultas SQL fuera de repositorios
DB::select("SELECT * FROM noticias WHERE ..."); // ← PROHIBIDO en Controllers/Handlers

// ❌ NUNCA: Operaciones de escritura múltiple sin DB::transaction()
$noticia = Noticia::create($data);
$noticia->etiquetas()->attach($ids); // ← si falla, la noticia queda sin etiquetas
```

### Lo que SIEMPRE se debe hacer

```php
// ✅ Handlers con múltiples writes usan DB::transaction()
public function handle(CreateNoticiaCommand $command): NoticiaDTO
{
    return DB::transaction(function () use ($command) {
        $noticia = $this->noticiaRepository->create([...]);
        $this->noticiaRepository->syncEtiquetas($noticia->id, $command->etiquetas);
        return NoticiaDTO::fromModel($noticia);
    });
}

// ✅ Controllers inyectan Handlers, no repositorios directamente
public function __construct(
    private readonly CreateNoticiaHandler $createHandler,
    private readonly GetNoticiasQueryHandler $getNoticiasHandler,
) {}

// ✅ Toda respuesta sale como DTO, nunca como modelo Eloquent raw
return response()->json(NoticiaDTO::fromModel($model));
```

---

## Patrón completo: ejemplo con Noticias

El módulo Noticias es la referencia canónica para crear nuevos módulos con slug y estado.

### 1. DTO (Application layer)

```php
// app/Application/Noticias/DTOs/NoticiaDTO.php
final readonly class NoticiaDTO
{
    public function __construct(
        public int $id,
        public string $titulo,
        public string $slug,
        public ?string $entradilla,
        public string $estado,
        public bool $destacada,
        public ?string $fecha_publicacion,
        public ?string $created_at,
    ) {}

    public static function fromModel(object $model): self
    {
        return new self(
            id: $model->id,
            titulo: $model->titulo,
            slug: $model->slug,
            entradilla: $model->entradilla,
            estado: $model->estado,
            destacada: (bool) $model->destacada,
            fecha_publicacion: $model->fecha_publicacion?->toIso8601String(),
            created_at: $model->created_at?->toIso8601String(),
        );
    }
}
```

### 2. Interface del repositorio (Domain layer)

```php
// app/Domain/Noticias/Contracts/NoticiaRepositoryInterface.php
interface NoticiaRepositoryInterface
{
    public function paginate(PaginationDTO $pagination): array;
    public function findById(int $id): mixed;
    public function findBySlug(string $slug): mixed;
    public function create(array $data): mixed;
    public function update(int $id, array $data): mixed;
    public function delete(int|array $ids): bool;
}
```

### 3. Excepción del dominio (Domain layer)

```php
// app/Domain/Noticias/Exceptions/NoticiaNotFoundException.php
class NoticiaNotFoundException extends \RuntimeException
{
    public function __construct(int|string $id)
    {
        parent::__construct("Noticia '{$id}' no encontrada.", 404);
    }
}
```

### 4. Command (Application layer)

```php
// app/Application/Noticias/Commands/CreateNoticiaCommand.php
final readonly class CreateNoticiaCommand
{
    public function __construct(
        public int $categoria_id,
        public int $autor_id,
        public string $titulo,
        public ?string $entradilla,
        public ?string $cuerpo,
        public string $estado,
        public bool $destacada,
    ) {}
}
```

### 5. Handler de escritura (Application layer)

```php
// app/Application/Noticias/Handlers/CreateNoticiaHandler.php
class CreateNoticiaHandler
{
    public function __construct(
        private readonly NoticiaRepositoryInterface $repository
    ) {}

    public function handle(CreateNoticiaCommand $command): NoticiaDTO
    {
        $model = $this->repository->create([
            'categoria_id' => $command->categoria_id,
            'autor_id'     => $command->autor_id,
            'titulo'       => $command->titulo,
            'entradilla'   => $command->entradilla,
            'cuerpo'       => $command->cuerpo,
            'estado'       => $command->estado,
            'destacada'    => $command->destacada,
        ]);

        return NoticiaDTO::fromModel($model);
    }
}
```

### 6. Query y QueryHandler (Application layer)

```php
// app/Application/Noticias/Queries/GetNoticiasQuery.php
final readonly class GetNoticiasQuery
{
    public function __construct(public PaginationDTO $pagination) {}
}

// app/Application/Noticias/QueryHandlers/GetNoticiasQueryHandler.php
class GetNoticiasQueryHandler
{
    public function __construct(
        private readonly NoticiaRepositoryInterface $repository
    ) {}

    public function handle(GetNoticiasQuery $query): array
    {
        return $this->repository->paginate($query->pagination);
    }
}
```

### 7. Repositorio Eloquent (Infrastructure layer)

```php
// app/Infrastructure/Noticias/Repositories/EloquentNoticiaRepository.php
class EloquentNoticiaRepository implements NoticiaRepositoryInterface
{
    public function paginate(PaginationDTO $pagination): array
    {
        $q = Noticia::query()->whereNull('deleted_at');

        if ($pagination->query) {
            $q->whereFullText(['titulo', 'entradilla', 'cuerpo'], $pagination->query);
        }

        $paginated = $q->orderBy('fecha_publicacion', 'desc')
            ->paginate($pagination->pageSize, ['*'], 'page', $pagination->pageIndex);

        return [
            'data'  => collect($paginated->items())->map(fn ($n) => NoticiaDTO::fromModel($n))->all(),
            'total' => $paginated->total(),
        ];
    }

    public function findBySlug(string $slug): NoticiaDTO
    {
        $noticia = Noticia::where('slug', $slug)->whereNull('deleted_at')->first();
        if (! $noticia) throw new NoticiaNotFoundException($slug);
        return NoticiaDTO::fromModel($noticia);
    }

    // ... resto de métodos
}
```

### 8. Modelo Eloquent (Infrastructure layer)

```php
// app/Infrastructure/Noticias/Models/Noticia.php
// - Slug se genera automáticamente en boot() usando Str::slug()
// - SoftDeletes en tablas críticas (deleted_at)
// - $fillable siempre explícito — nunca $guarded = []
// - Búsqueda FULLTEXT disponible con whereFullText()
```

### 9. Controller (Http layer)

```php
// app/Http/Controllers/Api/NoticiaController.php
class NoticiaController extends Controller
{
    public function __construct(
        private readonly GetNoticiasQueryHandler $getNoticiasHandler,
        private readonly GetNoticiaBySlugQueryHandler $getNoticiaBySlugHandler,
        private readonly CreateNoticiaHandler $createHandler,
        private readonly UpdateNoticiaHandler $updateHandler,
        private readonly DeleteNoticiaHandler $deleteHandler,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $pagination = PaginationDTO::fromArray([
            'pageIndex' => $request->get('pageIndex', 1),
            'pageSize'  => $request->get('pageSize', 10),
            'query'     => $request->get('query', ''),
            'sortKey'   => $request->input('sort.key', 'fecha_publicacion'),
            'sortOrder' => $request->input('sort.order', 'desc'),
        ]);

        return response()->json(
            $this->getNoticiasHandler->handle(new GetNoticiasQuery($pagination))
        );
    }

    public function store(StoreNoticiaRequest $request): JsonResponse
    {
        $dto = $this->createHandler->handle(new CreateNoticiaCommand(
            categoria_id: $request->categoria_id,
            autor_id:     auth()->id(),
            titulo:       $request->titulo,
            entradilla:   $request->entradilla,
            cuerpo:       $request->cuerpo,
            estado:       $request->estado ?? 'borrador',
            destacada:    $request->boolean('destacada', false),
        ));

        return response()->json($dto, 201);
    }
}
```

### 10. Bindear el repositorio en DomainServiceProvider

```php
// app/Providers/DomainServiceProvider.php
$this->app->bind(NoticiaRepositoryInterface::class, EloquentNoticiaRepository::class);
```

---

## Cómo crear un nuevo módulo paso a paso

Ejemplo: agregar el módulo `Secretarias`:

```text
1.  Domain/Secretarias/Contracts/SecretariaRepositoryInterface.php
2.  Domain/Secretarias/Exceptions/SecretariaNotFoundException.php
3.  Application/Secretarias/DTOs/SecretariaDTO.php
4.  Application/Secretarias/Commands/CreateSecretariaCommand.php
5.  Application/Secretarias/Commands/UpdateSecretariaCommand.php
6.  Application/Secretarias/Commands/DeleteSecretariaCommand.php
7.  Application/Secretarias/Handlers/CreateSecretariaHandler.php
8.  Application/Secretarias/Handlers/UpdateSecretariaHandler.php
9.  Application/Secretarias/Handlers/DeleteSecretariaHandler.php
10. Application/Secretarias/Queries/GetSecretariasQuery.php
11. Application/Secretarias/Queries/GetSecretariaBySlugQuery.php
12. Application/Secretarias/QueryHandlers/GetSecretariasQueryHandler.php
13. Application/Secretarias/QueryHandlers/GetSecretariaBySlugQueryHandler.php
14. Infrastructure/Secretarias/Models/Secretaria.php
15. Infrastructure/Secretarias/Repositories/EloquentSecretariaRepository.php
16. Http/Controllers/Api/SecretariaController.php
17. Http/Requests/Secretarias/StoreSecretariaRequest.php
18. Http/Requests/Secretarias/UpdateSecretariaRequest.php
19. Bindear en DomainServiceProvider
20. Registrar rutas en routes/api/v1.php
```

---

## Convenciones de nomenclatura

| Elemento | Convención | Ejemplo |
| --- | --- | --- |
| Commands | `{Accion}{Modulo}Command` | `CreateNoticiaCommand` |
| Handlers | `{Accion}{Modulo}Handler` | `CreateNoticiaHandler` |
| Queries | `Get{Modulo}Query` | `GetNoticiasQuery` |
| QueryHandlers | `Get{Modulo}QueryHandler` | `GetNoticiasQueryHandler` |
| DTOs | `{Modulo}DTO` | `NoticiaDTO` |
| Repositorios | `Eloquent{Modulo}Repository` | `EloquentNoticiaRepository` |
| Interfaces | `{Modulo}RepositoryInterface` | `NoticiaRepositoryInterface` |
| Excepciones | `{Modulo}NotFoundException` | `NoticiaNotFoundException` |
| Modelos | singular PascalCase | `Noticia`, `TipoNorma` |
| Tablas | snake_case plural | `noticias`, `tipos_norma` |
| Rutas API | kebab-case plural | `/api/v1/tipos-norma` |

---

## Manejo de errores

Las excepciones del dominio se transforman en respuestas HTTP en `bootstrap/app.php`.

| Código | Significado |
| --- | --- |
| `200` | Respuesta exitosa |
| `201` | Recurso creado |
| `204` | Eliminación exitosa (sin body) |
| `401` | No autenticado |
| `403` | Sin permisos (`permiso:recurso.accion`) |
| `404` | Recurso no encontrado (`NotFoundException`) |
| `422` | Validación fallida (Laravel FormRequest) |
| `500` | Error interno no controlado |

---

## Sistema de permisos

Los permisos usan el middleware `permiso:recurso.accion`:

```php
Route::get('/noticias', [NoticiaController::class, 'index'])
    ->middleware('permiso:noticias.ver');

Route::post('/noticias', [NoticiaController::class, 'store'])
    ->middleware('permiso:noticias.crear');
```

| Módulo | Permisos |
| --- | --- |
| usuarios | `usuarios.ver`, `usuarios.crear`, `usuarios.editar`, `usuarios.eliminar` |
| noticias | `noticias.ver`, `noticias.crear`, `noticias.editar`, `noticias.eliminar` |
| normas | `normas.ver`, `normas.crear`, `normas.editar` |
| tramites | `tramites.ver`, `tramites.crear`, `tramites.editar` |
| transparencia | `transparencia.ver`, `transparencia.crear` |
| reportes | `reportes.ver` |

---

## Slugs

Los slugs se auto-generan en el `boot()` del modelo Eloquent. **No los pases en el request.**

Modelos con slug: `Noticia`, `Comunicado`, `Evento`, `Norma`, `TramiteCatalogo`, `Secretaria`, `Autoridad`, `DocumentoTransparencia`

Los endpoints públicos usan `{slug}` como identificador en la URL (nunca `{id}`).

---

## Búsqueda de texto completo

Las tablas principales tienen índices FULLTEXT en MySQL. Usar `whereFullText()` en los repositorios:

```php
// En el repositorio Eloquent
if ($pagination->query) {
    $q->whereFullText(['titulo', 'entradilla', 'cuerpo'], $pagination->query);
}
```

Tablas con FULLTEXT: `noticias`, `comunicados`, `normas`, `tramites_catalogo`, `eventos`, `secretarias`, `autoridades`, `documentos_transparencia`

Para búsqueda global en todas las tablas, usar la vista `v_busqueda_global`.

---

## Paginación

Todos los endpoints de listado aceptan:

```text
GET /api/v1/noticias?pageIndex=1&pageSize=15&query=presupuesto&sort[key]=fecha_publicacion&sort[order]=desc
```

La respuesta siempre devuelve:

```json
{
  "data": [...],
  "total": 42
}
```

Usar siempre `->paginate()` de Eloquent en el repositorio. Nunca `->skip()->take()` manual.

---

## Configuraciones persistentes

El proyecto usa `spatie/laravel-settings` para configuraciones del sistema que el admin puede cambiar sin tocar código:

```php
// app/Settings/GeneralSettings.php
class GeneralSettings extends Settings
{
    public string $site_name;
    public bool $site_active;
    public string $contact_email;
    public int $items_per_page;
    public bool $maintenance_mode;

    public static function group(): string { return 'general'; }
}
```

Las settings se guardan en la tabla `settings`. No usar `config()` para datos que el negocio debe poder cambiar.

---

## Base de datos cenefco (legado SIASEC)

El proyecto incluye **145 migraciones** generadas a partir del sistema legado SIASEC (`disereco_siasec`) más **~18 migraciones nuevas** para la capa web institucional y el módulo de certificados. Todas conviven en la misma BD.

### Convención de nombres

Todos los archivos de migración del legado siguen el patrón:

```text
2026_04_14_NNNNNN_create_cenefco_{tabla}_table.php
```

Ejemplo: `2026_04_14_000134_create_cenefco_t_usuario_table.php`

### Grupos de tablas

| Grupo | Tablas principales |
| --- | --- |
| **Moodle** | `mdl_course`, `mdl_user` (+ logs) |
| **Usuarios** | `t_usuario`, `t_nivel`, `t_grupopermiso`, `t_usuariogrupopermiso`, `t_permiso` |
| **Académico** | `t_materia`, `t_plan`, `t_materia_plan`, `t_imparte`, `t_inscripcion`, `t_nota`, `t_horario` |
| **Pagos** | `t_pago`, `t_fechapago`, `t_tipopago`, `t_documento`, `t_fechadoc` |
| **Permisos** | `t_regcomponente`, `t_regform`, `t_funcionalidadform` |
| **Contenido web** | `t_pagina`, `t_modulo`, `t_menu`, `t_bloqueplantilla`, `t_bloqueajustable`, `t_seccionbloque` |
| **Catálogos** | `t_ciudad`, `t_universidad`, `t_carrera`, `t_profesion`, `t_tipoprograma`, `t_programa` |
| **Relaciones usuario** | `t_usuarioplan`, `t_usuarioprograma`, `t_usuariotipoprograma`, `t_usuarioplandoc` |
| **Logs** | Todas las tablas tienen su espejo `_log` (registran cambios históricos) |

### Convenciones de tablas legado (`t_*`, `mdl_*`)

- Los nombres originales **se conservan tal cual** (prefijo `t_` o `mdl_`). No se renombran.
- PKs compuestas: mayoría de tablas usa `PRIMARY KEY (id_campo, id_us_reg)` — se genera con `$table->primary([...])`.
- **No usar `$table->timestamps()`** en estas tablas — tienen `fecha_reg` propio.
- **No usar `$table->softDeletes()`** — usan `estado tinyint` (0=inactivo, 1=activo).
- Las tablas `_log` registran el historial de cambios y tienen un campo `tipo_log` varchar.
- `id_us_reg` = usuario que registró el dato (auditoría interna del sistema legado).

### Convenciones de tablas nuevas (`web_*`, `t_cert_*`)

Estas tablas siguen convenciones modernas de Laravel y son **incompatibles** con las del legado:

- `bigIncrements('id')` como PK simple — **nunca PK compuesta**.
- `timestampTz('created_at')`, `timestampTz('updated_at')`, `timestampTz('deleted_at')` — **usar `timestampTz`, no `timestamps()`** (sin timezone).
- Estado como `string` semántico: `borrador` / `publicado` / `archivado` — **no `tinyint`**.
- FK constraints declarados con `->foreign()` y `->index()` explícito.
- Slugs únicos en toda tabla con URL pública (`->unique()`).
- `boolean('activo')`, `boolean('destacado')`, `integer('orden')` — campos estándar de contenido web.

### Tablas `web_*` por prioridad

| Prioridad | Tablas |
|-----------|--------|
| 🔴 Crítico | `web_banner`, `web_configuracion_sitio`, `web_suscriptor`, `web_contacto_mensaje` |
| 🟠 Alta | `web_testimonio`, `web_faq`, `web_aliado`, `web_preinscripcion`, `web_descargable`, `web_descargable_registro`, `web_cifra_institucional`, `web_acreditacion` |
| 🟡 Media | `web_evento`, `web_docente_perfil`, `web_popup`, `web_etiqueta`, `web_articulo_etiqueta`, `web_programa_etiqueta`, `web_categoria_programa`, `web_programa_resena`, `web_galeria_video`, `web_hito_institucional`, `web_nota_prensa`, `web_redes_sociales`, `web_calendario_academico`, `web_whatsapp_grupo` |
| 🟢 Baja | `web_redireccion`, `web_galeria_categoria`, `web_notificacion_push`, `web_descuento_promocion` |

También se agregan campos web a tablas legado existentes: `t_articulo` (slug, SEO, vistas, destacada), `t_programa` (slug, SEO, destacado, orden), `t_pagina` (slug, contenido_html, SEO), `t_foto` (alt, orden), `t_boletin` (slug, imagen, SEO).

### Módulo de Certificados

Tablas propias del módulo (prefijo `t_cert_*`):

```text
t_cert_plantilla           → Plantilla JPG + configuración visual
t_cert_plantilla_campo     → Posición X/Y, fuente y estilo por campo
t_lista_aprobados          → Lista oficial de aprobados por apertura de curso
t_certificado              → Certificado generado con código único + QR
t_cert_verificacion        → Log de verificaciones públicas desde la web
```

- Código único: formato `cenefco-{AÑO}-{6 chars}` (ej: `cenefco-2026-A4X9K2`)
- Endpoint público: `GET /verificar/{codigo}` — devuelve VÁLIDO / ANULADO / NO ENCONTRADO
- Generación masiva: `CertificadoService::generarLote(imparteId, plantillaId)`
- Dependencias: `simplesoftwareio/simple-qrcode`, `intervention/image`

### Integración WhatsApp

Campo `whatsapp_grupo_url` en `t_imparte` (Opción A — un grupo por curso). Si un curso necesita múltiples grupos usar tabla `web_whatsapp_grupo` (Opción B). Campo `whatsapp_unido` en `t_inscripcion` para rastrear si el estudiante accedió al enlace.

### Ejecutar solo migraciones cenefco

```bash
# Correr únicamente las 145 migraciones del legado
php artisan migrate --path=database/migrations --filter=cenefco
```

### Relaciones clave entre tablas legado

```text
t_usuario         → t_nivel           (id_niv)
t_usuario         → t_universidad     (id_universidad)
t_usuario         → t_carrera         (id_carrera)
t_usuario         → t_tipoprograma    (id_tipoprograma)
t_usuariogrupopermiso → t_usuario     (id_us)
t_usuariogrupopermiso → t_grupopermiso(id_grupopermiso)
t_imparte         → t_usuario         (id_us — docente)
t_imparte         → t_materia         (id_mat)
t_inscripcion     → t_usuario         (id_us)
t_inscripcion     → t_imparte         (id_imp)
t_nota            → t_imparte + t_usuario + t_materia
t_pago            → t_usuario + t_fechapago
t_materia_plan    → t_materia + t_plan
t_permiso         → t_grupopermiso + t_regform
t_regform         → t_regcomponente
```

---

## Tests

```bash
make test                           # todos los tests
make test-filter f=CreateNoticiaTest # test específico
php artisan test --coverage         # con reporte de cobertura
```

- Tests de **Handlers** → unitarios con Mockery (mockear el repositorio)
- Tests de **Controllers/Endpoints** → feature tests con `RefreshDatabase`
- Los tests viven en `tests/Unit/` y `tests/Feature/`
- Usar factories para datos de prueba (`database/factories/`)
