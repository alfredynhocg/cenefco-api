<?php

namespace App\Http\Controllers\Api\WhatsApp;

use App\Http\Controllers\Controller;
use App\Http\Traits\WhatsAppServiceTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WhatsAppProductController extends Controller
{
    use WhatsAppServiceTrait;

    public function __construct()
    {
        $this->initializeWhatsAppService();
    }

    public function singleProduct(Request $request): JsonResponse
    {
        $data = $request->validate([
            'to' => 'required|string',
            'catalog_id' => 'required|string',
            'product_id' => 'required|string',
            'body' => 'string',
            'footer' => 'string',
        ]);

        return response()->json($this->wa->sendSingleProduct($data['to'], $data['catalog_id'], $data['product_id'], $data['body'] ?? '', $data['footer'] ?? ''));
    }

    public function multiProduct(Request $request): JsonResponse
    {
        $data = $request->validate([
            'to' => 'required|string',
            'catalog_id' => 'required|string',
            'header' => 'required|string',
            'body' => 'required|string',
            'footer' => 'string',
            'sections' => 'required|array|min:1',
            'sections.*.title' => 'required|string',
            'sections.*.products' => 'required|array|min:1',
            'sections.*.products.*.product_retailer_id' => 'required|string',
        ]);

        return response()->json($this->wa->sendMultiProduct($data['to'], $data['header'], $data['body'], $data['footer'] ?? '', $data['catalog_id'], $data['sections']));
    }
}
