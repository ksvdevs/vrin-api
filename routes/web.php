<?php

use App\Http\Controllers\GoogleCallbackController;
use App\Http\Controllers\GoogleDriveController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['status' => 'SGR API is running']);
});

Route::get('/api/auth/google/redirect', [GoogleCallbackController::class, 'redirect']);
Route::get('/api/auth/google/callback', [GoogleCallbackController::class, 'callback']);
Route::get('/api/drive/oauth/callback', [GoogleDriveController::class, 'callback']);
