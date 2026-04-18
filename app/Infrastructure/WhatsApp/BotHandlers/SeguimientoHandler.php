<?php

namespace App\Infrastructure\WhatsApp\BotHandlers;

use App\Infrastructure\Shared\Services\WhatsAppService;
use App\Infrastructure\WhatsApp\ConversationManager;
use App\Infrastructure\WhatsApp\Enums\BotButton;
use App\Infrastructure\WhatsApp\Enums\BotState;
use Illuminate\Support\Facades\DB;

class SeguimientoHandler
{
    public function __construct(
        private WhatsAppService $wa,
        private ConversationManager $conv,
        private MenuHandler $menu,
    ) {}

    public function pedirCI(string $from): void
    {
        $this->wa->sendText($from,
            "🔍 *Consulta de Pre-inscripción*\n\n".
            "Escribe tu *número de CI* para verificar el estado de tu solicitud.\n\n".
            '_Ejemplo: 5821034_'
        );
        $this->conv->setState($from, BotState::CONSULTA_CI->value);
    }

    public function buscarPorCI(string $from, string $texto): void
    {
        $ci = trim(preg_replace('/[^0-9A-Za-z]/', '', $texto));

        if (strlen($ci) < 5) {
            $this->wa->sendText($from, '⚠️ El CI ingresado no parece válido. Por favor escribe tu número de CI sin puntos ni guiones.');
            $this->wa->sendButtons($from, '¿Qué deseas hacer?', [
                ['id' => BotButton::INSCRIPCION->value, 'title' => '🔍 Intentar de nuevo'],
                ['id' => BotButton::MENU->value,        'title' => '🏠 Menú principal'],
            ]);

            return;
        }

        $preinscripciones = DB::table('web_preinscripcion')
            ->where('ci', $ci)
            ->join('t_programa', 'web_preinscripcion.programa_id', '=', 't_programa.id_programa')
            ->orderByDesc('web_preinscripcion.id')
            ->limit(5)
            ->get([
                'web_preinscripcion.id',
                'web_preinscripcion.nombre',
                'web_preinscripcion.apellido_paterno',
                'web_preinscripcion.apellido_materno',
                'web_preinscripcion.created_at',
                't_programa.nombre_programa',
            ]);

        if ($preinscripciones->isEmpty()) {
            $this->wa->sendText($from,
                "⚠️ No se encontró ninguna pre-inscripción con CI *{$ci}*.\n\n".
                'Verifica que hayas completado el formulario de pre-inscripción en nuestro portal web.'
            );
            $this->wa->sendButtons($from, '¿Qué deseas hacer?', [
                ['id' => BotButton::INSCRIPCION->value, 'title' => '📝 Info de inscripción'],
                ['id' => BotButton::SOPORTE->value,     'title' => '📞 Hablar con asesor'],
                ['id' => BotButton::MENU->value,        'title' => '🏠 Menú principal'],
            ]);
            $this->conv->setState($from, BotState::MENU->value);

            return;
        }

        $primera = $preinscripciones->first();
        $nombreCompleto = trim("{$primera->nombre} {$primera->apellido_paterno} {$primera->apellido_materno}");

        $texto = "✅ *Pre-inscripción(es) encontrada(s)*\n\n";
        $texto .= "👤 *Nombre:* {$nombreCompleto}\n\n";

        foreach ($preinscripciones as $i => $p) {
            $fecha = $p->created_at ? date('d/m/Y', strtotime($p->created_at)) : '-';
            $texto .= ($i + 1).". 📚 {$p->nombre_programa}\n";
            $texto .= "   📅 Registrado: {$fecha}\n\n";
        }

        $texto .= '⏳ Nuestro equipo revisará tu solicitud y te contactará próximamente.';

        $this->wa->sendText($from, $texto);

        $this->wa->sendButtons($from, '¿Necesitas algo más?', [
            ['id' => BotButton::SOPORTE->value, 'title' => '📞 Hablar con asesor'],
            ['id' => BotButton::MENU->value,    'title' => '🏠 Menú principal'],
        ]);

        $this->conv->setState($from, BotState::MENU->value);
    }
}
