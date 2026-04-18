<?php

namespace App\Http\Controllers\Api\Telegram;

use App\Http\Controllers\Controller;
use App\Http\Traits\TelegramServiceTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TelegramManagementController extends Controller
{
    use TelegramServiceTrait;

    public function __construct()
    {
        $this->initializeTelegramServices();
    }

    public function setWebhook(Request $request): JsonResponse
    {
        $url = $request->query('url');

        if (! $url) {
            return response()->json(['error' => 'Falta el parámetro "url"'], 422);
        }

        $secret = config('telegram.webhook_secret');
        $response = $this->telegram->setWebhook($url, $secret);

        return response()->json($response);
    }

    public function webhookInfo(): JsonResponse
    {
        return response()->json($this->telegram->getWebhookInfo());
    }

    public function deleteWebhook(): JsonResponse
    {
        return response()->json($this->telegram->deleteWebhook());
    }

    public function botTest(Request $request): JsonResponse
    {
        $chatId = $request->query('chat_id');

        if (! $chatId) {
            return response()->json(['error' => 'Falta el parámetro "chat_id"'], 422);
        }

        $this->bot->sendBienvenida($chatId);

        return response()->json(['status' => 'bot menu sent', 'chat_id' => $chatId]);
    }

    public function me(): JsonResponse
    {
        return response()->json($this->telegram->getMe());
    }
}
