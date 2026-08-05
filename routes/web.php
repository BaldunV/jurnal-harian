<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\JournalController;
use App\Http\Controllers\TeacherController;

// Auth Routes (Guest)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Protected Routes (Auth Required)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Siswa Routes
    Route::get('/', [JournalController::class, 'dashboard']);
    Route::get('/dashboard', [JournalController::class, 'dashboard'])->name('dashboard');
    Route::post('/journal/save', [JournalController::class, 'save'])->name('journal.save');
    Route::get('/history', [JournalController::class, 'history'])->name('history');
    Route::get('/api/journal/{date}', [JournalController::class, 'getByDate'])->name('journal.by_date');
    Route::get('/statistics', [JournalController::class, 'statistics'])->name('statistics');
    Route::get('/profile', [JournalController::class, 'profile'])->name('profile');
    Route::post('/profile/update', [JournalController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/password', [JournalController::class, 'updatePassword'])->name('profile.password');

    // Guru Routes
    Route::get('/teacher', [TeacherController::class, 'index'])->name('teacher.index');
    Route::get('/api/teacher/student/{id}', [TeacherController::class, 'studentDetail'])->name('teacher.student_detail');
});
