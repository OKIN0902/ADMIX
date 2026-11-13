<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
});

Route::get('/ping', function () {
    return response()->json([
        'message' => 'ADMIX API aktif',
        'server_time' => microtime(true),
    ]);
});

Route::get('/diagnostic', function () {
    $start = microtime(true);

    // Pekerjaan minimal untuk memastikan route berjalan.
    $message = 'ADMIX diagnostic berhasil';

    $execution_ms = round(
        (microtime(true) - $start) * 1000,
        3
    );

    return response()->json([
        'message' => $message,
        'handler_ms' => $execution_ms,
    ]);
});