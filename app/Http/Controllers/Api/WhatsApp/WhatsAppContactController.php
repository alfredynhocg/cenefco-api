<?php

namespace App\Http\Controllers\Api\WhatsApp;

use App\Http\Controllers\Controller;
use App\Http\Traits\WhatsAppServiceTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WhatsAppContactController extends Controller
{
    use WhatsAppServiceTrait;

    public function __construct()
    {
        $this->initializeWhatsAppService();
    }

    public function contacts(Request $request): JsonResponse
    {
        $data = $request->validate([
            'to' => 'required|string',
            'contacts' => 'required|array|min:1',
            'contacts.*.first_name' => 'required|string',
            'contacts.*.last_name' => 'string',
            'contacts.*.phone' => 'required|string',
        ]);

        return response()->json($this->wa->sendContacts($data['to'], $data['contacts']));
    }
}
