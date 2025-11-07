<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AlbumController;
use App\Http\Controllers\GuestAlbumController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\QRCodeController;
use App\Http\Controllers\UploadController;

/*
|--------------------------------------------------------------------------
| Test & Debug
|--------------------------------------------------------------------------
*/
Route::get('test', fn() => response()->json(['message' => 'Hello world']));
Route::get('/debug', function (Request $request) {
    return [
        'session_id' => session()->getId(),
        'auth_check' => auth()->check(),
        'auth_id' => auth()->id(),
        'session_token' => session()->token(),
    ];
});
/*
|--------------------------------------------------------------------------
| Public routes (no authentication)
|--------------------------------------------------------------------------
*/

// Registration & login
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Guest access (PIN & uploads)
Route::prefix('guest')->group(function () {
    Route::get('/{album_id}', [GuestAlbumController::class, 'show']);          // album info
    Route::post('/{album_id}/verify-pin', [GuestAlbumController::class, 'verifyPin']); // PIN verification
    Route::get('/{album_id}/media', [GuestAlbumController::class, 'media']);  // list media
    Route::post('/{album_id}/upload', [GuestAlbumController::class, 'upload']); // upload media
});

/*
|--------------------------------------------------------------------------
| Protected routes for album owners (auth:sanctum)
|--------------------------------------------------------------------------
*/
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
