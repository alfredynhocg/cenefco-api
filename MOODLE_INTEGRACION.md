# Integración Laravel ↔ Moodle

## 1. Instalación de Moodle en Ubuntu (Apache + PHP 8.3 + MySQL 8)

### 1.1 Extensiones PHP requeridas

```bash
sudo apt install -y \
  php8.3-opcache libapache2-mod-php8.3 \
  php8.3-curl php8.3-gd php8.3-intl php8.3-mbstring \
  php8.3-xml php8.3-zip php8.3-soap php8.3-xmlrpc php8.3-mysql
sudo systemctl restart apache2
```

### 1.2 Descargar Moodle

```bash
cd /var/www
sudo git clone --depth=1 -b MOODLE_404_STABLE https://github.com/moodle/moodle.git moodle
sudo mkdir -p /var/moodledata
sudo chown -R www-data:www-data /var/www/moodle /var/moodledata
sudo chmod 755 /var/www/moodle
sudo chmod 770 /var/moodledata
```

> Rama `MOODLE_404_STABLE` = Moodle 4.4 LTS, compatible con PHP 8.3.

### 1.3 Base de datos MySQL

```bash
sudo mysql
```

```sql
CREATE DATABASE moodle DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'moodleuser'@'localhost' IDENTIFIED BY 'TU_PASSWORD';
GRANT ALL PRIVILEGES ON moodle.* TO 'moodleuser'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

> **Nota:** En Ubuntu, MySQL root usa autenticación por socket. Usar `sudo mysql` sin contraseña.
> Si da `Access denied`, recuperar acceso con:
> ```bash
> sudo mysqld --skip-grant-tables --skip-networking --user=mysql &
> mysql -u root
> # Dentro:
> FLUSH PRIVILEGES;
> ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'TU_PASSWORD';
> EXIT;
> sudo pkill mysqld && sudo systemctl start mysql
> ```

### 1.4 VirtualHost Apache

```bash
sudo nano /etc/apache2/sites-available/moodle.conf
```

```apache
<VirtualHost *:80>
    ServerName moodle.local
    DocumentRoot /var/www/moodle
    <Directory /var/www/moodle>
        Options +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

```bash
sudo a2ensite moodle.conf
sudo a2enmod rewrite
sudo systemctl reload apache2
echo "127.0.0.1  moodle.local" | sudo tee -a /etc/hosts
```

### 1.5 Ajustar PHP

```bash
sudo sed -i 's/^;*max_input_vars.*/max_input_vars = 5000/' /etc/php/8.3/apache2/php.ini
sudo sed -i 's/^upload_max_filesize.*/upload_max_filesize = 100M/' /etc/php/8.3/apache2/php.ini
sudo sed -i 's/^post_max_size.*/post_max_size = 100M/' /etc/php/8.3/apache2/php.ini
sudo systemctl restart apache2
```

### 1.6 Instalador web

Abrir en el navegador: **http://moodle.local**

| Campo | Valor |
|-------|-------|
| Directorio de datos | `/var/moodledata` |
| Host BD | `localhost` |
| Base de datos | `moodle` |
| Usuario BD | `moodleuser` |
| Contraseña BD | la que definiste |

### 1.7 Temas premium

Si el tema se ve sin estilos, verificar compatibilidad de versión:

```bash
cat /var/www/moodle/theme/NOMBRE_TEMA/version.php
cat /var/www/moodle/version.php | grep release
```

El campo `$plugin->requires` del tema debe ser compatible con la versión instalada de Moodle. Instalar siempre el ZIP del tema que corresponda a la versión exacta de Moodle.

---

## 2. Configurar servicios web en Moodle

### 2.1 Habilitar servicios web y protocolo REST

Ir a:
```
http://moodle.local/admin/settings.php?section=webservicesoverview
```

Seguir el checklist:
1. **Habilitar Servicios Web** → activar checkbox → Guardar
2. **Habilitar los protocolos** → activar **REST** → Guardar

### 2.2 Crear servicio personalizado

```
http://moodle.local/admin/settings.php?section=externalservices
```

Clic en **"Añadir"** y completar:

| Campo | Valor |
|-------|-------|
| Nombre | `Laravel API` |
| Nombre corto | `laravel_api` |
| Habilitado | ✅ |
| Únicamente usuarios autorizados | ❌ |

### 2.3 Agregar funciones al servicio

Una vez creado el servicio, agregar las funciones necesarias:

| Función | Descripción |
|---------|-------------|
| `core_course_create_courses` | Crear cursos |
| `core_course_get_courses` | Listar cursos |
| `core_course_delete_courses` | Eliminar cursos |
| `core_user_create_users` | Crear usuarios |
| `core_user_get_users` | Listar usuarios |
| `enrol_manual_enrol_users` | Inscribir usuarios a cursos |

### 2.4 Generar token de acceso

```
http://moodle.local/admin/webservice/tokens.php
```

Crear token:
- **Usuario**: Administrador
- **Servicio**: `Laravel API`

Guardar el token generado — se usará en Laravel.

### 2.5 Verificar que funciona

```bash
curl -g "http://moodle.local/webservice/rest/server.php?wstoken=TU_TOKEN&wsfunction=core_course_get_courses&moodlewsrestformat=json"
```

Debe devolver un JSON con los cursos existentes.

---

## 3. Integración en Laravel (DDD + CQRS)

### 3.1 Variables de entorno

Agregar al `.env`:

```env
MOODLE_URL=http://moodle.local
MOODLE_TOKEN=TU_TOKEN_AQUI
```

### 3.2 Configuración en `config/services.php`

```php
'moodle' => [
    'url'   => env('MOODLE_URL', 'http://moodle.local'),
    'token' => env('MOODLE_TOKEN'),
],
```

### 3.3 Estructura de archivos creados

```text
app/
├── Domain/Moodle/
│   └── Contracts/
│       └── MoodleCourseRepositoryInterface.php
│
├── Application/Moodle/
│   ├── Commands/
│   │   └── CreateMoodleCourseCommand.php
│   ├── DTOs/
│   │   └── MoodleCourseDTO.php
│   ├── Handlers/
│   │   └── CreateMoodleCourseHandler.php
│   └── QueryHandlers/
│       └── GetMoodleCoursesQueryHandler.php
│
├── Infrastructure/Moodle/
│   ├── MoodleClient.php                        ← cliente HTTP hacia Moodle
│   └── Repositories/
│       └── MoodleCourseRepository.php
│
└── Http/Controllers/Api/
    └── MoodleCourseController.php
```

### 3.4 MoodleClient

`app/Infrastructure/Moodle/MoodleClient.php`

```php
<?php

namespace App\Infrastructure\Moodle;

use RuntimeException;

class MoodleClient
{
    private string $url;
    private string $token;

    public function __construct()
    {
        $this->url   = rtrim(config('services.moodle.url'), '/');
        $this->token = config('services.moodle.token');
    }

    public function call(string $function, array $params = []): mixed
    {
        $url = "{$this->url}/webservice/rest/server.php"
            . "?wstoken={$this->token}"
            . "&wsfunction={$function}"
            . "&moodlewsrestformat=json";

        foreach ($params as $key => $value) {
            $url .= '&' . $key . '=' . urlencode((string) $value);
        }

        $body = file_get_contents($url);
        $data = json_decode($body, true);

        if (isset($data['exception'])) {
            throw new RuntimeException("[Moodle] {$data['message']} ({$data['exception']})");
        }

        return $data;
    }
}
```

> **Nota:** Se usa `file_get_contents` en lugar de `Http::` de Laravel porque Guzzle re-encodea
> los corchetes de los parámetros (`courses[0][fullname]` → `courses%5B0%5D%5Bfullname%5D`),
> lo que hace que Moodle no reconozca el token ni los parámetros.

### 3.5 Interface del repositorio

`app/Domain/Moodle/Contracts/MoodleCourseRepositoryInterface.php`

```php
<?php

namespace App\Domain\Moodle\Contracts;

interface MoodleCourseRepositoryInterface
{
    public function create(array $data): array;
    public function getAll(): array;
    public function getById(int $id): array;
    public function delete(int $id): void;
}
```

### 3.6 Repositorio Moodle

`app/Infrastructure/Moodle/Repositories/MoodleCourseRepository.php`

```php
<?php

namespace App\Infrastructure\Moodle\Repositories;

use App\Domain\Moodle\Contracts\MoodleCourseRepositoryInterface;
use App\Infrastructure\Moodle\MoodleClient;

class MoodleCourseRepository implements MoodleCourseRepositoryInterface
{
    public function __construct(private readonly MoodleClient $client) {}

    public function create(array $data): array
    {
        $result = $this->client->call('core_course_create_courses', [
            'courses[0][fullname]'   => $data['fullname'],
            'courses[0][shortname]'  => $data['shortname'],
            'courses[0][categoryid]' => $data['categoryid'] ?? 1,
            'courses[0][summary]'    => $data['summary'] ?? '',
            'courses[0][format]'     => $data['format'] ?? 'topics',
            'courses[0][visible]'    => $data['visible'] ?? 1,
        ]);

        return $result[0] ?? [];
    }

    public function getAll(): array
    {
        return $this->client->call('core_course_get_courses') ?? [];
    }

    public function getById(int $id): array
    {
        $result = $this->client->call('core_course_get_courses', [
            'options[ids][0]' => $id,
        ]);

        return $result[0] ?? [];
    }

    public function delete(int $id): void
    {
        $this->client->call('core_course_delete_courses', [
            'courseids[0]' => $id,
        ]);
    }
}
```

### 3.7 DTO

`app/Application/Moodle/DTOs/MoodleCourseDTO.php`

```php
<?php

namespace App\Application\Moodle\DTOs;

final readonly class MoodleCourseDTO
{
    public function __construct(
        public int $id,
        public string $fullname,
        public string $shortname,
        public int $categoryid,
        public string $summary,
        public int $visible,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id:         $data['id'] ?? 0,
            fullname:   $data['fullname'] ?? '',
            shortname:  $data['shortname'] ?? '',
            categoryid: $data['categoryid'] ?? 1,
            summary:    strip_tags($data['summary'] ?? ''),
            visible:    $data['visible'] ?? 1,
        );
    }
}
```

### 3.8 Command y Handler

`app/Application/Moodle/Commands/CreateMoodleCourseCommand.php`

```php
<?php

namespace App\Application\Moodle\Commands;

final readonly class CreateMoodleCourseCommand
{
    public function __construct(
        public string $fullname,
        public string $shortname,
        public int $categoryid = 1,
        public string $summary = '',
        public string $format = 'topics',
        public int $visible = 1,
    ) {}
}
```

`app/Application/Moodle/Handlers/CreateMoodleCourseHandler.php`

```php
<?php

namespace App\Application\Moodle\Handlers;

use App\Application\Moodle\Commands\CreateMoodleCourseCommand;
use App\Application\Moodle\DTOs\MoodleCourseDTO;
use App\Domain\Moodle\Contracts\MoodleCourseRepositoryInterface;

class CreateMoodleCourseHandler
{
    public function __construct(
        private readonly MoodleCourseRepositoryInterface $repository
    ) {}

    public function handle(CreateMoodleCourseCommand $command): MoodleCourseDTO
    {
        $result = $this->repository->create([
            'fullname'   => $command->fullname,
            'shortname'  => $command->shortname,
            'categoryid' => $command->categoryid,
            'summary'    => $command->summary,
            'format'     => $command->format,
            'visible'    => $command->visible,
        ]);

        return MoodleCourseDTO::fromArray($result);
    }
}
```

### 3.9 Controller

`app/Http/Controllers/Api/MoodleCourseController.php`

```php
<?php

namespace App\Http\Controllers\Api;

use App\Application\Moodle\Commands\CreateMoodleCourseCommand;
use App\Application\Moodle\Handlers\CreateMoodleCourseHandler;
use App\Application\Moodle\QueryHandlers\GetMoodleCoursesQueryHandler;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
            'fullname'   => 'required|string|max:255',
            'shortname'  => 'required|string|max:100',
            'categoryid' => 'sometimes|integer',
            'summary'    => 'sometimes|string',
        ]);

        $dto = $this->createHandler->handle(new CreateMoodleCourseCommand(
            fullname:   $request->fullname,
            shortname:  $request->shortname,
            categoryid: $request->integer('categoryid', 1),
            summary:    $request->string('summary', '')->toString(),
        ));

        return response()->json($dto, 201);
    }
}
```

### 3.10 Rutas

`routes/api/v1.php`

```php
// Moodle
Route::middleware(['auth:sanctum'])->prefix('moodle')->group(function () {
    Route::get('/courses', [\App\Http\Controllers\Api\MoodleCourseController::class, 'index']);
    Route::post('/courses', [\App\Http\Controllers\Api\MoodleCourseController::class, 'store']);
});
```

### 3.11 Binding en DomainServiceProvider

`app/Providers/DomainServiceProvider.php`

```php
$this->app->bind(
    \App\Domain\Moodle\Contracts\MoodleCourseRepositoryInterface::class,
    \App\Infrastructure\Moodle\Repositories\MoodleCourseRepository::class
);
```

---

## 4. Endpoints disponibles

| Método | Endpoint | Descripción | Auth |
|--------|----------|-------------|------|
| `GET` | `/api/v1/moodle/courses` | Listar todos los cursos de Moodle | Bearer token |
| `POST` | `/api/v1/moodle/courses` | Crear un curso en Moodle | Bearer token |

### Ejemplo: listar cursos

```bash
curl -X GET http://localhost:8000/api/v1/moodle/courses \
  -H "Authorization: Bearer TU_TOKEN_SANCTUM"
```

### Ejemplo: crear curso

```bash
curl -X POST http://localhost:8000/api/v1/moodle/courses \
  -H "Authorization: Bearer TU_TOKEN_SANCTUM" \
  -H "Content-Type: application/json" \
  -d '{
    "fullname": "Diplomado en Finanzas",
    "shortname": "DIP-FIN-2026",
    "categoryid": 1,
    "summary": "Descripción del diplomado"
  }'
```

### Respuesta exitosa

```json
{
  "id": 5,
  "fullname": "Diplomado en Finanzas",
  "shortname": "DIP-FIN-2026",
  "categoryid": 1,
  "summary": "Descripción del diplomado",
  "visible": 1
}
```

---

## 5. Solución de problemas

| Problema | Causa | Solución |
|----------|-------|----------|
| `invalidtoken` en Moodle | Token generado para servicio incorrecto | Crear token para el servicio **Laravel API**, no para Moodle mobile |
| Respuesta `null` del cliente | Guzzle re-encodea los corchetes de parámetros | Usar `file_get_contents` en `MoodleClient` |
| `core_course_get_courses` devuelve vacío | Servicios web no habilitados | Habilitar en `webservicesoverview` pasos 1 y 2 |
| Tema premium sin estilos | Versión del tema incompatible con Moodle | Instalar el ZIP del tema para la versión exacta de Moodle |
| `Access denied` en MySQL | Root usa `auth_socket` o tiene contraseña | Usar `sudo mysql` o recuperar con `--skip-grant-tables` |
