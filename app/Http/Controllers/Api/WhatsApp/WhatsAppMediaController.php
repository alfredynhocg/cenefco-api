<?php

namespace App\Http\Controllers\Api\WhatsApp;

use App\Http\Controllers\Controller;
use App\Http\Traits\WhatsAppServiceTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WhatsAppMediaController extends Controller
{
    use WhatsAppServiceTrait;

    public function __construct()
    {
        $this->initializeWhatsAppService();
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
