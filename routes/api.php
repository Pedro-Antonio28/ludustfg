<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\StudentAuthController;
use App\Http\Controllers\Api\Auth\TeacherAuthController;
use App\Http\Controllers\Api\Auth\DirectorAuthController;
use App\Http\Controllers\Api\Student\ClassesController as SClassesController;
use App\Http\Controllers\Api\Student\TestController;
use App\Http\Controllers\Api\Teacher\ClassesController as TClassesController;

// Obtener usuario autenticado con guard adecuado
Route::middleware('auth:student')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/student/register', [StudentAuthController::class, 'register']);
Route::post('/student/login', [StudentAuthController::class, 'login']);

Route::post('/teacher/register', [TeacherAuthController::class, 'register']);
Route::post('/teacher/login', [TeacherAuthController::class, 'login']);

Route::post('/directors/register', [DirectorAuthController::class, 'register']);

Route::post('/logout', [AuthController::class, 'logout']);

// Rutas para estudiantes con Bearer token (auth:student)
Route::prefix('student')->middleware(['auth:student'])->group(function () {
    Route::get('/dashboard', [SClassesController::class, 'index']);
    Route::get('/classes/{id}', [SClassesController::class, 'show']);
    Route::get('/classes/{id}/tests', [TestController::class, 'index']);
});

// Rutas para profesores (puedes duplicar esto con auth:teacher si usas tokens personales para ellos)


// ✅ Rutas del profesor
Route::prefix('teacher')->middleware(['auth:sanctum', EnsureRoleGuard::class . ':teacher'])->group(function () {
    Route::get('/dashboard', [TClassesController::class, 'index']);
});



