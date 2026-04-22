<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\VerifyEmailController;
use App\Http\Controllers\Api\PagoController;
use Illuminate\Support\Facades\Route;

Route::get('/ping', fn () => response()->json(['status' => 'ok', 'message' => 'API funcionando']));

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:login');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])
        ->middleware('throttle:forgot-password');
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

Route::middleware('auth:sanctum')->prefix('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/perfil', [AuthController::class, 'updatePerfil']);
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware('auth:sanctum')->prefix('email')->group(function () {
    Route::get('/verify/{id}/{hash}', [VerifyEmailController::class, 'verify'])
        ->middleware('signed')
        ->name('verification.verify');
    Route::post('/resend', [VerifyEmailController::class, 'resend'])
        ->middleware('throttle:6,1');
});

Route::prefix('v1')->group(base_path('routes/api/v1.php'));

Route::post('/stripe/webhook', [PagoController::class, 'webhook'])
    ->withoutMiddleware(['auth:sanctum']);

Route::prefix('whatsapp')->group(base_path('routes/api/whatsapp.php'));
Route::prefix('telegram')->group(base_path('routes/api/telegram.php'));
