<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AlbumController;
use App\Http\Controllers\GuestAlbumController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\QRCodeController;
use App\Http\Controllers\UploadController;

/*Route::get('/debug', function (Request $request) {
    return [
        'session_id' => session()->getId(),
        'auth_check' => auth()->check(),
        'auth_id' => auth()->id(),
        'session_token' => session()->token(),
    ];
});*/


Route::get('test', fn() => response()->json(['message' => 'Hello world']));

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('api.login');

Route::prefix('guest')->group(function () {
    Route::get('/{album_id}', [GuestAlbumController::class, 'show']);
    Route::post('/{album_id}/verify-pin', [GuestAlbumController::class, 'verifyPin']);
    Route::get('/{album_id}/media', [GuestAlbumController::class, 'media']);
    Route::post('/{album_id}/upload', [UploadController::class, 'upload']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/albums', [AlbumController::class, 'index']);
    Route::post('/albums', [AlbumController::class, 'store']);
    Route::put('/albums/{album_id}', [AlbumController::class, 'update']);
    Route::delete('/albums/{album_id}', [AlbumController::class, 'destroy']);

    Route::post('/albums/{album_id}/upload', [UploadController::class, 'upload']);
    Route::post('/albums/{album_id}/export', [AlbumController::class, 'exportAlbum']);
    Route::get('/albums/{album_id}/export/progress', [AlbumController::class, 'progress']);

    // Media management
    Route::get('/media/{album}/media', [MediaController::class, 'index']);
    Route::get('/media/{album_id}/{filename}', [MediaController::class, 'show']);
    /* Route::get('/media/pending/{album_id}', [MediaController::class, 'pending']);
     Route::post('/media/{mediaId}/approve', [MediaController::class, 'approve']);*/

    // QR code display
    Route::get('/albums/{album_id}/qrcode', [QRCodeController::class, 'show']);
});


