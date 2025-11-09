<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AlbumController;
use App\Http\Controllers\GuestAlbumController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\QRCodeController;
use App\Http\Controllers\UploadController;


Route::get('test', fn() => response()->json(['message' => 'Hello world']));
Route::get('/debug', function (Request $request) {
    return [
        'session_id' => session()->getId(),
        'auth_check' => auth()->check(),
        'auth_id' => auth()->id(),
        'session_token' => session()->token(),
    ];
});

Route::get('/test-redis', function () {
    try {
        \Illuminate\Support\Facades\Redis::set('test_key', 'ok', 'EX', 10);
        $value = \Illuminate\Support\Facades\Redis::get('test_key');
        return response()->json(['success' => true, 'value' => $value]);
    } catch (Exception $e) {
        return response()->json(['success' => false, 'error' => $e->getMessage()]);
    }
});

Route::get('/test-hetzner', function () {
    try {
        $filePath = 'test.txt';
        \Illuminate\Support\Facades\Storage::disk('hetzner')->put($filePath, 'Hello Hetzner');
        $content = \Illuminate\Support\Facades\Storage::disk('hetzner')->get($filePath);
        \Illuminate\Support\Facades\Storage::disk('hetzner')->delete($filePath);
        return response()->json(['success' => true, 'content' => $content]);
    } catch (Exception $e) {
        return response()->json(['success' => false, 'error' => $e->getMessage()]);
    }
});


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::prefix('guest')->group(function () {
    Route::get('/{album_id}', [GuestAlbumController::class, 'show']);
    Route::post('/{album_id}/verify-pin', [GuestAlbumController::class, 'verifyPin']);
    Route::get('/{album_id}/media', [GuestAlbumController::class, 'media']);
    Route::post('/{album_id}/upload', [UploadController::class, 'upload']);
});

Route::middleware('auth:sanctum')->group(function () {

    // Album CRUD
    Route::get('/albums', [AlbumController::class, 'index']);
    Route::post('/albums', [AlbumController::class, 'store']);
    Route::put('/albums/{album_id}', [AlbumController::class, 'update']);
    Route::delete('/albums/{album_id}', [AlbumController::class, 'destroy']);

    // Owner uploads
    Route::post('/albums/{album_id}/upload', [UploadController::class, 'upload']);

    // Export & progress
    Route::post('/albums/{album_id}/export', [AlbumController::class, 'export']);
    Route::get('/albums/{album_id}/export/progress', [AlbumController::class, 'progress']);

    // Media management
    Route::get('/media', [MediaController::class, 'index']);
    Route::get('/media/pending', [MediaController::class, 'pending']);
    Route::post('/media/{mediaId}/approve', [MediaController::class, 'approve']);

    // QR code display
    Route::get('/albums/{album_id}/qrcode', [QRCodeController::class, 'show']);
});
