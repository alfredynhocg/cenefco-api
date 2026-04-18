<?php

namespace App\Infrastructure\Shared\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Prism;
use Prism\Prism\ValueObjects\Messages\UserMessage;

class AgentService
{
    private string $model;

    private int $maxTokens;

    private float $temperature;

    private int $maxInputChars;

    private int $rateLimit;

    private array $forbidden = [
        'ignore previous',
        'forget instructions',
        'system prompt',
        'ignore all',
        'new instructions',
        'you are now',
        'act as',
        'pretend you',
        'jailbreak',
        'dan mode',
        'ignore your',
        'disregard',
        'override',
        'bypass',
        'do anything now',
        'sin restricciones',
        'ignora tus instrucciones',
        'olvida tus instrucciones',
        'actua como',
        'finge ser',
    ];

    public function __construct()
    {
        $this->model = config('bot.ia.model', 'qwen2.5:7b');
        $this->maxTokens = config('bot.ia.max_tokens', 512);
        $this->temperature = config('bot.ia.temperature', 0.3);
        $this->maxInputChars = config('bot.ia.max_input_chars', 500);
        $this->rateLimit = config('bot.ia.rate_limit', 20);
    }

    public function responder(string $phone, string $userInput): ?string
    {
        $input = $this->sanitize($userInput);

        if (! $this->checkRateLimit($phone)) {
            return '⏳ Estás enviando muchos mensajes. Por favor espera un momento.';
        }

        if ($this->esJailbreak($input)) {
            Log::warning('[AgentService] Intento de jailbreak bloqueado', [
                'phone' => $phone,
                'input' => $input,
            ]);

            return 'Lo siento, no puedo procesar esa solicitud.';
        }

        $contexto = Cache::remember('bot_ia_contexto', 300, fn () => $this->buildContext());
        $prompt = $this->buildSystemPrompt($contexto);

        $inicio = microtime(true);
        $output = null;

        try {
            $response = Prism::text()
                ->using(Provider::Ollama, $this->model)
                ->withSystemPrompt($prompt)
                ->withMessages([new UserMessage($input)])
                ->withMaxTokens($this->maxTokens)
                ->usingTemperature($this->temperature)
                ->asText();

            $output = trim($response->text);

            $this->logInteraccion(
                phone: $phone,
                input: $input,
                prompt: $prompt,
                output: $output,
                tokensIn: $response->usage->promptTokens ?? null,
                tokensOut: $response->usage->completionTokens ?? null,
                latencia: (int) ((microtime(true) - $inicio) * 1000),
                error: false,
            );

            return $output ?: null;

        } catch (\Throwable $e) {
            Log::error('[AgentService] Error Ollama', [
                'phone' => $phone,
                'input' => $input,
                'error' => $e->getMessage(),
            ]);

            $this->logInteraccion(
                phone: $phone,
                input: $input,
                prompt: $prompt,
                output: null,
                tokensIn: null,
                tokensOut: null,
                latencia: (int) ((microtime(true) - $inicio) * 1000),
                error: true,
            );

            return null;
        }
    }

    private function sanitize(string $input): string
    {
        $input = strip_tags($input);
        $input = mb_substr(trim($input), 0, $this->maxInputChars);

        return $input;
    }

    private function esJailbreak(string $input): bool
    {
        $normalized = strtolower($input);
        $normalized = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $normalized) ?: $normalized;
        $normalized = preg_replace('/\s+/', ' ', trim($normalized));

        foreach ($this->forbidden as $pattern) {
            if (str_contains($normalized, $pattern)) {
                return true;
            }
        }

        return false;
    }

    private function checkRateLimit(string $phone): bool
    {
        $key = "wa_throttle_ia:{$phone}";
        $count = (int) Cache::get($key, 0);

        if ($count >= $this->rateLimit) {
            return false;
        }

        Cache::put($key, $count + 1, 60);

        return true;
    }

    private function buildContext(): string
    {
        $nombre = config('bot.cenefco.nombre');
        $sigla = config('bot.cenefco.sigla');

        $programas = DB::table('t_programa')
            ->join('t_tipoprograma', 't_programa.id_tipoprograma', '=', 't_tipoprograma.id_tipoprograma')
            ->where('t_programa.estado', 1)
            ->whereNull('t_programa.deleted_at')
            ->orderBy('t_programa.orden')
            ->limit(10)
            ->get(['t_programa.nombre_programa', 't_tipoprograma.nombre_tipoprograma', 't_programa.inicio_inscripciones'])
            ->map(fn ($p) => "- {$p->nombre_programa} ({$p->nombre_tipoprograma})"
                .($p->inicio_inscripciones && $p->inicio_inscripciones !== '2000-01-01'
                    ? ', inscripciones desde '.date('d/m/Y', strtotime($p->inicio_inscripciones))
                    : '')
            )
            ->implode("\n");

        $docentes = DB::table('web_docente_perfil')
            ->whereNull('deleted_at')
            ->where('mostrar_en_web', 1)
            ->where('estado', 'publicado')
            ->orderBy('orden')
            ->limit(8)
            ->get(['nombre_completo', 'titulo_academico', 'especialidad'])
            ->map(fn ($d) => "- {$d->nombre_completo}"
                .($d->titulo_academico ? " ({$d->titulo_academico})" : '')
                .($d->especialidad ? " — {$d->especialidad}" : '')
            )
            ->implode("\n");

        $eventos = DB::table('web_evento')
            ->whereNull('deleted_at')
            ->where('estado', 'publicado')
            ->where('fecha_inicio', '>=', now())
            ->orderBy('fecha_inicio')
            ->limit(5)
            ->get(['titulo', 'fecha_inicio', 'lugar', 'modalidad'])
            ->map(fn ($e) => "- {$e->titulo}"
                .($e->fecha_inicio ? ' ('.date('d/m/Y', strtotime($e->fecha_inicio)).')' : '')
                .($e->lugar ? " en {$e->lugar}" : '')
                .($e->modalidad ? " [{$e->modalidad}]" : '')
            )
            ->implode("\n");

        $horarios = collect(config('bot.cenefco.horarios'))
            ->map(fn ($h, $d) => "{$d}: {$h}")
            ->implode(', ');

        $contacto = config('bot.cenefco.contacto');
        $ubicacion = config('bot.cenefco.ubicacion');

        return <<<CONTEXT
                INSTITUCIÓN: {$nombre} ({$sigla})
                DIRECCIÓN: {$ubicacion['direccion']}
                HORARIOS DE ATENCIÓN: {$horarios}
                TELÉFONO: {$contacto['telefono']}
                EMAIL: {$contacto['email']}
                WEB: {$contacto['web']}

                PROGRAMAS Y CURSOS DISPONIBLES:
                {$programas}

                PLANTA DOCENTE:
                {$docentes}

                PRÓXIMOS EVENTOS:
                {$eventos}
                CONTEXT;
    }

    private function buildSystemPrompt(string $contexto): string
    {
        $nombre = config('bot.cenefco.nombre');

        return <<<SYSTEM
                Eres el asistente virtual oficial de *{$nombre}*, un centro de formación continua y posgrado. Respondes preguntas de estudiantes y personas interesadas por WhatsApp.

                REGLAS ESTRICTAS:
                1. Responde ÚNICAMENTE con información del contexto provisto. Si no tienes la información, indica al usuario que se comunique directamente con la institución o solicite soporte.
                2. Responde siempre en español, de forma clara, amable y concisa (máximo 3-4 oraciones).
                3. Nunca inventes datos, precios, fechas ni requisitos que no estén en el contexto.
                4. No respondas preguntas fuera del ámbito académico y de formación continua.
                5. Usa formato simple sin markdown complejo — el mensaje irá por WhatsApp.
                6. Si el usuario quiere hablar con una persona, indícale que puede solicitar soporte escribiendo "soporte".
                7. Si preguntan por un programa específico, indícales que seleccionen "Programas y Cursos" en el menú para ver todos los detalles.

                CONTEXTO ACTUALIZADO DE LA INSTITUCIÓN:
                {$contexto}
                SYSTEM;
    }

    private function logInteraccion(
        string $phone,
        string $input,
        string $prompt,
        ?string $output,
        ?int $tokensIn,
        ?int $tokensOut,
        int $latencia,
        bool $error,
    ): void {
        try {
            DB::table('whatsapp_ia_logs')->insert([
                'phone' => $phone,
                'input' => $input,
                'prompt' => mb_substr($prompt, 0, 5000),
                'output' => $output ? mb_substr($output, 0, 2000) : null,
                'modelo' => $this->model,
                'tokens_in' => $tokensIn,
                'tokens_out' => $tokensOut,
                'latencia_ms' => $latencia,
                'error' => $error,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('[AgentService] No se pudo registrar interacción', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
