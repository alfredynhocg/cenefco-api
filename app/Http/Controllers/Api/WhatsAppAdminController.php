<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Infrastructure\Shared\Services\WhatsAppService;
use App\Infrastructure\WhatsApp\Models\WhatsappConversacion;
use App\Infrastructure\WhatsApp\Models\WhatsappEtiqueta;
use App\Infrastructure\WhatsApp\Models\WhatsappMensaje;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WhatsAppAdminController extends Controller
{
    public function conversaciones(Request $request): JsonResponse
    {
        $q = WhatsappConversacion::query()
            ->with('etiquetas')
            ->orderByRaw("CASE WHEN estado = 'soporte' THEN 0 ELSE 1 END")
            ->orderByDesc('updated_at');

        if ($search = $request->get('query')) {
            $q->where(function ($sub) use ($search) {
                $sub->where('phone', 'like', "%{$search}%")
                    ->orWhereHas('cliente', fn ($c) => $c->where('nombre', 'like', "%{$search}%")
                        ->orWhere('razon_social', 'like', "%{$search}%")
                    );
            });
        }

        if ($estado = $request->get('estado')) {
            $q->where('estado', $estado);
        }

        if ($etiquetaId = $request->get('etiqueta_id')) {
            $q->whereHas('etiquetas', fn ($sub) => $sub->where('whatsapp_etiquetas.id', $etiquetaId));
        }

        $pageSize = (int) $request->get('pageSize', 15);
        $pageIndex = (int) $request->get('pageIndex', 1);

        $paginated = $q->paginate($pageSize, ['*'], 'page', $pageIndex);

        $asesorIds = collect($paginated->items())->pluck('asesor_id')->filter()->unique()->values();
        $asesores = $asesorIds->isNotEmpty()
            ? \Illuminate\Support\Facades\DB::table('web_asesor')->whereIn('id', $asesorIds)->get()->keyBy('id')
            : collect();

        $data = collect($paginated->items())->map(fn ($c) => [
            'id' => $c->id,
            'phone' => $c->phone,
            'nombre' => $c->nombre,
            'estado' => $c->estado,
            'contexto' => $c->contexto,
            'cliente_id' => $c->cliente_id,
            'asesor_id' => $c->asesor_id,
            'asesor' => $c->asesor_id ? $asesores->get($c->asesor_id) : null,
            'etiquetas' => $c->etiquetas->map(fn ($e) => [
                'id' => $e->id,
                'nombre' => $e->nombre,
                'color' => $e->color,
            ])->all(),
            'updated_at' => $c->updated_at?->toIso8601String(),
            'created_at' => $c->created_at?->toIso8601String(),
        ])->all();

        return response()->json([
            'data' => $data,
            'total' => $paginated->total(),
        ]);
    }

    public function mensajes(int $id): JsonResponse
    {
        $conv = WhatsappConversacion::findOrFail($id);

        $mensajes = WhatsappMensaje::where('conversacion_id', $conv->id)
            ->orderBy('created_at')
            ->get(['id', 'direccion', 'tipo', 'contenido', 'created_at']);

        $data = $mensajes->map(fn ($m) => [
            'id' => $m->id,
            'direccion' => $m->direccion,
            'tipo' => $m->tipo,
            'contenido' => $m->contenido,
            'created_at' => $m->created_at?->toIso8601String(),
        ])->all();

        $asesor = $conv->asesor_id
            ? \Illuminate\Support\Facades\DB::table('web_asesor')->where('id', $conv->asesor_id)->first()
            : null;

        return response()->json([
            'data' => $data,
            'phone' => $conv->phone,
            'nombre' => $conv->nombre,
            'estado' => $conv->estado,
            'asesor_id' => $conv->asesor_id,
            'asesor' => $asesor,
        ]);
    }

    public function marcarAtendido(int $id): JsonResponse
    {
        $conv = WhatsappConversacion::findOrFail($id);
        $conv->update(['estado' => 'menu', 'contexto' => []]);

        return response()->json(['message' => 'Conversación marcada como atendida.']);
    }

    public function todosPhones(): JsonResponse
    {
        $phones = WhatsappConversacion::orderByDesc('updated_at')
            ->pluck('phone')
            ->unique()
            ->values();

        return response()->json(['phones' => $phones]);
    }

    public function enviar(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string',
            'mensaje' => 'required|string',
        ]);

        $service = new WhatsAppService;

        try {
            $result = $service->sendText($request->phone, $request->mensaje);

            return response()->json(['message' => 'Mensaje enviado correctamente.', 'result' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Error al enviar: '.$e->getMessage()], 500);
        }
    }

    public function enviarMasivo(Request $request): JsonResponse
    {
        $request->validate([
            'phones' => 'required|array|min:1',
            'phones.*' => 'required|string',
            'mensaje' => 'required|string',
        ]);

        $service = new WhatsAppService;
        $exitosos = 0;
        $detalleFallidos = [];

        foreach ($request->phones as $phone) {
            try {
                $service->sendText($phone, $request->mensaje);
                $exitosos++;
            } catch (\Throwable $e) {
                $detalleFallidos[] = ['phone' => $phone, 'error' => $e->getMessage()];
            }
        }

        return response()->json([
            'exitosos' => $exitosos,
            'fallidos' => count($detalleFallidos),
            'detalle_fallidos' => $detalleFallidos,
        ]);
    }

    public function enviarMedia(Request $request): JsonResponse
    {
        $request->validate([
            'phones' => 'required|array|min:1',
            'phones.*' => 'required|string',
            'tipo' => 'required|in:image,document',
            'archivo' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'caption' => 'nullable|string|max:1024',
            'filename' => 'nullable|string|max:255',
        ]);

        $service = new WhatsAppService;

        try {
            $path = $request->file('archivo')->getRealPath();
            $upload = $service->uploadMedia($path);
            $mediaId = $upload['id'] ?? null;
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Error al subir el archivo: '.$e->getMessage()], 500);
        }

        if (! $mediaId) {
            return response()->json(['message' => 'WhatsApp no devolvió un media_id. Verifica el formato del archivo.'], 500);
        }

        $exitosos = 0;
        $detalleFallidos = [];
        $caption = $request->get('caption', '');
        $filename = $request->get('filename', $request->file('archivo')->getClientOriginalName());

        foreach ($request->phones as $phone) {
            try {
                if ($request->tipo === 'image') {
                    $service->sendImage($phone, $mediaId, $caption);
                } else {
                    $service->sendDocument($phone, $mediaId, $caption, $filename);
                }
                $exitosos++;
            } catch (\Throwable $e) {
                $detalleFallidos[] = ['phone' => $phone, 'error' => $e->getMessage()];
            }
        }

        return response()->json([
            'exitosos' => $exitosos,
            'fallidos' => count($detalleFallidos),
            'detalle_fallidos' => $detalleFallidos,
        ]);
    }

    public function etiquetas(): JsonResponse
    {
        $etiquetas = WhatsappEtiqueta::orderBy('nombre')->get(['id', 'nombre', 'color']);

        return response()->json(['data' => $etiquetas]);
    }

    public function crearEtiqueta(Request $request): JsonResponse
    {
        $request->validate([
            'nombre' => 'required|string|max:80|unique:whatsapp_etiquetas,nombre',
            'color' => 'nullable|string|regex:/^#[0-9a-fA-F]{6}$/',
        ]);

        $etiqueta = WhatsappEtiqueta::create([
            'nombre' => $request->nombre,
            'color' => $request->get('color', '#6366f1'),
        ]);

        return response()->json($etiqueta, 201);
    }

    public function actualizarEtiqueta(Request $request, int $id): JsonResponse
    {
        $etiqueta = WhatsappEtiqueta::findOrFail($id);

        $request->validate([
            'nombre' => "required|string|max:80|unique:whatsapp_etiquetas,nombre,{$id}",
            'color' => 'nullable|string|regex:/^#[0-9a-fA-F]{6}$/',
        ]);

        $etiqueta->update([
            'nombre' => $request->nombre,
            'color' => $request->get('color', $etiqueta->color),
        ]);

        return response()->json($etiqueta);
    }

    public function eliminarEtiqueta(int $id): JsonResponse
    {
        WhatsappEtiqueta::findOrFail($id)->delete();

        return response()->json(null, 204);
    }

    public function asignarEtiquetas(Request $request, int $id): JsonResponse
    {
        $conv = WhatsappConversacion::findOrFail($id);

        $request->validate([
            'etiqueta_ids' => 'required|array',
            'etiqueta_ids.*' => 'integer|exists:whatsapp_etiquetas,id',
        ]);

        $conv->etiquetas()->sync($request->etiqueta_ids);

        $etiquetas = $conv->etiquetas()->get(['whatsapp_etiquetas.id', 'nombre', 'color']);

        return response()->json(['etiquetas' => $etiquetas]);
    }

    public function enviarPlantilla(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string',
            'plantilla' => 'required|in:confirmacion,entrega,promocion',
            'params' => 'required|array',
        ]);

        $service = new WhatsAppService;
        $phone = $request->phone;
        $params = $request->params;

        try {
            $result = match ($request->plantilla) {
                'confirmacion' => $service->sendTemplate($phone, 'confirmacion_pedido', 'es', [
                    ['type' => 'text', 'text' => $params['numero_pedido'] ?? ''],
                    ['type' => 'text', 'text' => $params['total'] ?? ''],
                ]),
                'entrega' => $service->sendTemplate($phone, 'estado_entrega', 'es', [
                    ['type' => 'text', 'text' => $params['numero_pedido'] ?? ''],
                ]),
                'promocion' => $service->sendTemplate($phone, 'promocion_especial', 'es', [
                    ['type' => 'text', 'text' => $params['descuento'] ?? ''],
                    ['type' => 'text', 'text' => $params['fecha_fin'] ?? ''],
                ]),
            };

            return response()->json(['message' => 'Plantilla enviada correctamente.', 'result' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Error al enviar la plantilla: '.$e->getMessage()], 500);
        }
    }
}
