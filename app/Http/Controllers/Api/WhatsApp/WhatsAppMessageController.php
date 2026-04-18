<?php

namespace App\Http\Controllers\Api\WhatsApp;

use App\Http\Controllers\Controller;
use App\Http\Traits\WhatsAppServiceTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WhatsAppMessageController extends Controller
{
    use WhatsAppServiceTrait;

    public function __construct()
    {
        $this->initializeWhatsAppService();
    }

    public function text(Request $request): JsonResponse
    {
        $data = $request->validate([
            'to' => 'required|string',
            'message' => 'required|string',
            'preview_url' => 'boolean',
        ]);

        return response()->json($this->wa->sendText($data['to'], $data['message'], $data['preview_url'] ?? false));
    }

    public function markAsRead(Request $request): JsonResponse
    {
        $data = $request->validate(['message_id' => 'required|string']);

        return response()->json($this->wa->markAsRead($data['message_id']));
    }

    public function react(Request $request): JsonResponse
    {
        $data = $request->validate([
            'to' => 'required|string',
            'message_id' => 'required|string',
            'emoji' => 'required|string',
        ]);

        return response()->json($this->wa->reactToMessage($data['to'], $data['message_id'], $data['emoji']));
    }
}
