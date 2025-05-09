<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\DirectorAuthController;
use App\Http\Controllers\Api\Auth\StudentAuthController;
use App\Http\Controllers\Api\Student\ClassesController as SClassesController;
use App\Http\Controllers\Api\Teacher\ClassesController as TClassesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/register/student', [StudentAuthController::class, 'register']);
Route::post('/login/student', [StudentAuthController::class, 'login']);

Route::post('/directors/register', [DirectorAuthController::class, 'register']);

Route::post('/logout', [AuthController::class, 'logout']);

Route::get('/students/dashboard', [SClassesController::class, 'index'])
    ->middleware('auth:sanctum');

Route::get('/teachers/dashboard', [TClassesController::class, 'index'])
    ->middleware('auth:sanctum');

