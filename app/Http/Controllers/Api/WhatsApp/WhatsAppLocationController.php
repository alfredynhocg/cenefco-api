<?php

namespace App\Http\Controllers\Api\WhatsApp;

use App\Http\Controllers\Controller;
use App\Http\Traits\WhatsAppServiceTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WhatsAppLocationController extends Controller
{
    use WhatsAppServiceTrait;

    public function __construct()
    {
        $this->initializeWhatsAppService();
    }

    public function location(Request $request): JsonResponse
    {
        $data = $request->validate([
            'to' => 'required|string',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'name' => 'string',
            'address' => 'string',
        ]);

        return response()->json($this->wa->sendLocation($data['to'], $data['lat'], $data['lng'], $data['name'] ?? '', $data['address'] ?? ''));
    }

    public function locationRequest(Request $request): JsonResponse
    {
        $data = $request->validate(['to' => 'required|string', 'body' => 'string']);

        return response()->json($this->wa->sendLocationRequest($data['to'], $data['body'] ?? '¿Dónde te encuentras?'));
    }
}
