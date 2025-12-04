<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '^(?!api).*$');
// routes/web.php
//Route::get('/login', [AuthController::class, 'login'])->name('login');

