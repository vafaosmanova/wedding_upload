<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/{any}', function () {
    return view('index');
})->where('any', '^(?!api).*$');

Route::get('/login', [AuthController::class, 'login'])->name('login');

