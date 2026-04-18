<?php

namespace App\Http\Controllers\Api\WhatsApp;

use App\Http\Controllers\Controller;
use App\Services\WhatsAppService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Netflie\WhatsAppCloudApi\WhatsAppCloudApi;

class WhatsAppSendController extends Controller
{
    private WhatsAppService $wa;

    public function __construct()
    {
        $this->wa = new WhatsAppService(new WhatsAppCloudApi([
            'from_phone_number_id' => config('whatsapp.phone_number_id'),
            'access_token' => config('whatsapp.access_token'),
        ]));
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

    public function document(Request $request): JsonResponse
    {
        $data = $request->validate([
            'to' => 'required|string',
            'source' => 'required|string',
            'caption' => 'string',
            'filename' => 'string',
        ]);

        return response()->json($this->wa->sendDocument($data['to'], $data['source'], $data['caption'] ?? '', $data['filename'] ?? ''));
    }

    public function audio(Request $request): JsonResponse
    {
        $data = $request->validate(['to' => 'required|string', 'source' => 'required|string']);

        return response()->json($this->wa->sendAudio($data['to'], $data['source']));
    }

    public function image(Request $request): JsonResponse
    {
        $data = $request->validate([
            'to' => 'required|string',
            'source' => 'required|string',
            'caption' => 'string',
        ]);

        return response()->json($this->wa->sendImage($data['to'], $data['source'], $data['caption'] ?? ''));
    }

    public function video(Request $request): JsonResponse
    {
        $data = $request->validate([
            'to' => 'required|string',
            'source' => 'required|string',
            'caption' => 'string',
        ]);

        return response()->json($this->wa->sendVideo($data['to'], $data['source'], $data['caption'] ?? ''));
    }

    public function sticker(Request $request): JsonResponse
    {
        $data = $request->validate(['to' => 'required|string', 'source' => 'required|string']);

        return response()->json($this->wa->sendSticker($data['to'], $data['source']));
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

    public function buttons(Request $request): JsonResponse
    {
        $data = $request->validate([
            'to' => 'required|string',
            'body' => 'required|string',
            'header' => 'string',
            'footer' => 'string',
            'buttons' => 'required|array|min:1|max:3',
            'buttons.*.id' => 'required|string',
            'buttons.*.title' => 'required|string',
        ]);

        return response()->json($this->wa->sendButtons($data['to'], $data['body'], $data['buttons'], $data['header'] ?? '', $data['footer'] ?? ''));
    }

    public function list(Request $request): JsonResponse
    {
        $data = $request->validate([
            'to' => 'required|string',
            'header' => 'required|string',
            'body' => 'required|string',
            'footer' => 'string',
            'button_text' => 'required|string',
            'sections' => 'required|array|min:1',
            'sections.*.title' => 'required|string',
            'sections.*.rows' => 'required|array|min:1',
            'sections.*.rows.*.id' => 'required|string',
            'sections.*.rows.*.title' => 'required|string',
            'sections.*.rows.*.description' => 'string',
        ]);

        return response()->json($this->wa->sendList($data['to'], $data['header'], $data['body'], $data['footer'] ?? '', $data['button_text'], $data['sections']));
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

    public function uploadMedia(Request $request): JsonResponse
    {
        $request->validate(['file' => 'required|file']);
        $path = $request->file('file')->getRealPath();
        $response = $this->wa->uploadMedia($path);

        return response()->json($response);
    }

    public function downloadMedia(string $mediaId): \Illuminate\Http\Response
    {
        $content = $this->wa->downloadMedia($mediaId);

        return response($content, 200)->header('Content-Type', 'application/octet-stream');
    }
}
