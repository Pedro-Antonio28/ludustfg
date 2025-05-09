<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\DirectorAuthController;
use App\Http\Controllers\Api\Auth\StudentAuthController;
use App\Http\Controllers\Api\Auth\TeacherAuthController;
use App\Http\Controllers\Api\Student\ClassesController as SClassesController;
use App\Http\Controllers\Api\Teacher\ClassesController as TClassesController;
use App\Http\Middleware\EnsureRoleGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/student/register', [StudentAuthController::class, 'register']);
Route::post('/student/login', [StudentAuthController::class, 'login']);

Route::post('/teacher/register', [TeacherAuthController::class, 'register']);
Route::post('/teacher/login', [TeacherAuthController::class, 'login']);

Route::post('/directors/register', [DirectorAuthController::class, 'register']);

Route::post('/logout', [AuthController::class, 'logout']);

Route::prefix('student')->middleware(['auth:sanctum', EnsureRoleGuard::class . ':student',])->group(function () {
    Route::get('/dashboard', [SClassesController::class, 'index']);
});

Route::prefix('teacher')->middleware(['auth:sanctum', EnsureRoleGuard::class . ':teacher',])->group(function () {
    Route::get('/dashboard', [TClassesController::class, 'index']);
});

