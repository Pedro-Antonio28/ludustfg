<?php

use App\Http\Controllers\Api\Auth\DirectorAuthController;
use App\Http\Controllers\Api\Auth\StudentAuthController;
use App\Http\Controllers\Api\StudentClassesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/register', [StudentAuthController::class, 'register']);
Route::post('/login', [StudentAuthController::class, 'login']);

Route::post('/directors/register', [DirectorAuthController::class, 'register']);

Route::get('/students/dashboard', [StudentClassesController::class, 'index'])
    ->middleware('auth:sanctum');

