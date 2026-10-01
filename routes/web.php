<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['status' => 'SGR API is running']);
});

Route::get('/api/auth/google/redirect', [\App\Http\Controllers\GoogleCallbackController::class, 'redirect']);
Route::get('/api/auth/google/callback', [\App\Http\Controllers\GoogleCallbackController::class, 'callback']);
