<?php

use App\Http\Controllers\Api\DirectorAuthController;
use App\Http\Controllers\Api\StudentAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/register', [StudentAuthController::class, 'register']);
Route::post('/login', [StudentAuthController::class, 'login']);

Route::post('/directors/register', [DirectorAuthController::class, 'register']);


Route::get('/auth/google/redirect', function () {
    return Socialite::driver('google')->stateless()->redirect();
});

Route::get('/auth/google/callback', function () {
    try {
        $googleUser = Socialite::driver('google')->stateless()->user();

        $user = User::firstOrCreate(
            ['email' => $googleUser->getEmail()],
            [
                'name' => $googleUser->getName(),
                'password' => Hash::make(Str::random(24)),
            ]
        );

        $token = $user->createToken('google-token')->plainTextToken;

        return redirect("http://localhost:5180/login?token={$token}&name=" . urlencode($user->name) . "&email=" . urlencode($user->email));
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Google login failed',
            'message' => $e->getMessage(),
        ], 500);
    }
});
