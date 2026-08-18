<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminStudentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\JournalController;
use App\Http\Controllers\TeacherController;
use Illuminate\Support\Facades\Route;

// Auth Routes (Guest)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Protected Routes (Auth Required)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', function () {
        return match (auth()->user()->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'guru' => redirect()->route('teacher.index'),
            default => redirect()->route('dashboard'),
        };
    });

    // Siswa Routes
    Route::middleware('role:siswa')->group(function () {
        Route::get('/dashboard', [JournalController::class, 'dashboard'])->name('dashboard');
        Route::post('/journal/save', [JournalController::class, 'save'])->name('journal.save');
        Route::get('/history', [JournalController::class, 'history'])->name('history');
        Route::get('/api/journal/{date}', [JournalController::class, 'getByDate'])->name('journal.by_date');
        Route::get('/statistics', [JournalController::class, 'statistics'])->name('statistics');
        Route::get('/profile', [JournalController::class, 'profile'])->name('profile');
        Route::post('/profile', [JournalController::class, 'updateProfile'])->name('profile.update');
        Route::post('/profile/password', [JournalController::class, 'updatePassword'])->name('profile.password');
        Route::get('/mascot', [JournalController::class, 'mascot'])->name('mascot');
    });

    Route::get('/admin', [AdminController::class, 'dashboard'])->middleware('role:admin')->name('admin.dashboard');

    Route::middleware('role:admin')->group(function () {
        Route::post('/admin/students/bulk', [AdminStudentController::class, 'bulkStore'])->name('admin.students.bulk');
        Route::post('/admin/students/import/preview', [AdminStudentController::class, 'importPreview'])->name('admin.students.import.preview');
        Route::post('/admin/students/import/store', [AdminStudentController::class, 'importStore'])->name('admin.students.import.store');
        Route::get('/admin/students/template', [AdminStudentController::class, 'downloadTemplate'])->name('admin.students.template');
    });

    Route::middleware('role:admin,guru')->group(function () {
        Route::get('/teacher', [TeacherController::class, 'index'])->name('teacher.index');
        Route::get('/api/teacher/student/{id}', [TeacherController::class, 'studentDetail'])->name('teacher.student_detail');
    });

});
