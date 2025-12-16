<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AlbumController;
use App\Http\Controllers\GuestAlbumController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\QRCodeController;
use App\Http\Controllers\UploadController;

Route::get('/debug', function (Request $request) {
    return [
        'session_id' => session()->getId(),
        'auth_check' => auth()->check(),
        'auth_id' => auth()->id(),
        'session_token' => session()->token(),
    ];
});
//AUTH
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('api.login');
Route::middleware('auth:sanctum')->group(function () {
    //CRUD
    Route::get('/albums', [AlbumController::class, 'index']);
    Route::post('/albums', [AlbumController::class, 'store']);
    Route::put('/albums/{album_id}', [AlbumController::class, 'update']);
    Route::delete('/albums/{album_id}', [AlbumController::class, 'destroy']);
    //QR-CODE
    Route::get('/albums/{album_id}/qrcode', [QRCodeController::class, 'show']);
    //UPLOAD
    Route::post('/albums/{album_id}/upload', [UploadController::class, 'upload']);
    //EXPORT
    Route::post('/albums/{album_id}/export', [AlbumController::class, 'exportAlbum']);
    Route::get('/albums/{album_id}/export/progress', [AlbumController::class, 'progress']);
    Route::get('/albums/{album_id}/export/download', [AlbumController::class, 'downloadZip']);
    //MEDIA LIST (owner)
    Route::get('/albums/{album_id}/media', [MediaController::class, 'pending']);
    //MEDIA STREAM (owner)
    Route::get('/media/{media_id}/stream', [MediaController::class, 'streamOwnerMedia'])
        ->name('owner.media.stream');
    //PENDING MEDIA
    Route::get('/media/pending/{album_id}', [MediaController::class, 'pending']);
    Route::post('/media/{media_id}/approve', [MediaController::class, 'approve']);
    //DELETE MEDIA
    Route::delete('/media/{media_id}', [MediaController::class, 'destroy']);
});
//GAST
Route::prefix('guest')->group(function () {
    Route::get('/{album_id}/media/stream/{type}/{media_id}', [GuestAlbumController::class, 'streamMedia'])
        ->name('guest.media.stream');
    Route::get('/{album_id}', [GuestAlbumController::class, 'show']);
    Route::post('/{album_id}/verify-pin', [GuestAlbumController::class, 'verifyPin']);
    Route::get('/{album_id}/media', [GuestAlbumController::class, 'media']);
    Route::get('/albums/{album_id}/guest/download', [GuestAlbumController::class, 'downloadZip']);
    Route::post('/{album_id}/upload', [UploadController::class, 'upload'])->defaults('isGuest', true);
});
