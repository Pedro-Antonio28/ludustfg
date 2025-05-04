<?php

use App\Http\Controllers\Api\DirectorAuthController;
use App\Http\Controllers\Api\StudentAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/register', [StudentAuthController::class, 'register']);

Route::post('/directors/register', [DirectorAuthController::class, 'register']);
