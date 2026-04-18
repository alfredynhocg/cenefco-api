<?php

namespace App\Http\Controllers\Api\WhatsApp;

use App\Http\Controllers\Controller;
use App\Http\Traits\WhatsAppServiceTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WhatsAppTemplateController extends Controller
{
    use WhatsAppServiceTrait;

    public function __construct()
    {
        $this->initializeWhatsAppService();
    }

    public function template(Request $request): JsonResponse
    {
        $data = $request->validate([
            'to' => 'required|string',
            'template' => 'required|string',
            'language' => 'required|string',
            'parameters' => 'array',
        ]);

        return response()->json($this->wa->sendTemplate($data['to'], $data['template'], $data['language'], $data['parameters'] ?? []));
    }
}
