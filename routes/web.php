<?php

use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\OtpController;
use Illuminate\Support\Facades\Route;

Route::get('/', [OtpController::class, 'index'])->name('otp.index');

Route::post('/fetch-otp', [OtpController::class, 'fetch'])
    ->middleware('throttle:6,1')
    ->name('otp.fetch');

// === BARU: endpoint JSON untuk refresh tanpa reload ===
Route::post('/fetch-otp/json', [OtpController::class, 'fetchJson'])
    ->middleware('throttle:20,1')
    ->name('otp.fetch.json');

Route::get('/google/connect/{account}', [GoogleAuthController::class, 'redirect'])
    ->name('google.connect');

Route::get('/google/callback', [GoogleAuthController::class, 'callback'])
    ->name('google.callback');