<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Middleware\HandleApiExceptions;
use Illuminate\Support\Facades\Route;

Route::middleware(HandleApiExceptions::class)->group(function (): void {
    Route::prefix('auth')->group(function (): void {
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:mobile-login');
        Route::post('/otp/verify', [AuthController::class, 'verifyOtp'])->middleware('throttle:mobile-otp');
        Route::post('/otp/resend', [AuthController::class, 'resendOtp'])->middleware('throttle:mobile-otp-resend');
    });

    Route::middleware(['auth:sanctum', 'abilities:student', 'role:siswa', 'throttle:mobile-api'])
        ->prefix('auth')
        ->group(function (): void {
            Route::get('/me', [AuthController::class, 'me']);
            Route::post('/logout', [AuthController::class, 'logout']);
        });
});
