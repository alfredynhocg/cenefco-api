<?php

namespace App\Http\Controllers\Api\WhatsApp;

use App\Http\Controllers\Controller;
use App\Http\Traits\WhatsAppServiceTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WhatsAppProfileController extends Controller
{
    use WhatsAppServiceTrait;

    public function __construct()
    {
        $this->initializeWhatsAppService();
    }

    public function getProfile(): JsonResponse
    {
        return response()->json($this->wa->getBusinessProfile());
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $data = $request->validate([
            'about' => 'string',
            'address' => 'string',
            'description' => 'string',
            'email' => 'email',
            'websites' => 'array',
        ]);

        return response()->json($this->wa->updateBusinessProfile($data));
    }
}
