<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\JournalController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\StatisticsController;
use App\Http\Middleware\HandleApiExceptions;
use Illuminate\Support\Facades\Route;

Route::middleware(HandleApiExceptions::class)->group(function (): void {
    Route::prefix('auth')->group(function (): void {
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:mobile-login');
        Route::post('/otp/verify', [AuthController::class, 'verifyOtp'])->middleware('throttle:mobile-otp');
        Route::post('/otp/resend', [AuthController::class, 'resendOtp'])->middleware('throttle:mobile-otp-resend');
    });

    Route::middleware(['auth:sanctum', 'abilities:student', 'role:siswa', 'throttle:mobile-api'])
        ->group(function (): void {
            Route::prefix('auth')->group(function (): void {
                Route::get('/me', [AuthController::class, 'me']);
                Route::post('/logout', [AuthController::class, 'logout']);
            });

            Route::prefix('me')->group(function (): void {
                Route::get('/journal/today', [JournalController::class, 'today']);
                Route::post('/journal', [JournalController::class, 'store']);
                Route::put('/journal', [JournalController::class, 'update']);
                Route::post('/journal/submit', [JournalController::class, 'submit']);
                Route::get('/journals', [JournalController::class, 'index']);
                Route::get('/journals/{journal}', [JournalController::class, 'show'])
                    ->whereNumber('journal');
                Route::post('/journals/{journal}/photos/{type}', [JournalController::class, 'uploadPhoto'])
                    ->whereNumber('journal')
                    ->whereIn('type', ['olahraga', 'makan']);
                Route::get('/journals/{journal}/photos/{type}', [JournalController::class, 'photo'])
                    ->whereNumber('journal')
                    ->whereIn('type', ['olahraga', 'makan'])
                    ->name('api.me.journals.photos.show');

                Route::get('/statistics', [StatisticsController::class, 'show']);
                Route::get('/profile', [ProfileController::class, 'show']);
                Route::put('/profile', [ProfileController::class, 'update']);
                Route::post('/profile/photo', [ProfileController::class, 'uploadPhoto']);
                Route::get('/profile/photo', [ProfileController::class, 'photo'])
                    ->name('api.me.profile.photo.show');
                Route::post('/change-password', [ProfileController::class, 'changePassword']);
            });
        });
});
