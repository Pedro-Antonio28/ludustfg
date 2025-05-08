<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\Director;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DirectorAuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|unique:directors,email',
            'password'         => 'required|string|min:6',
            'profile_img'  => 'nullable|string',
            'school_name'  => 'required|string',
            'school_code'  => 'required|string',
            'school_email' => 'required|email|unique:directors,school_email',
            'school_tel'   => 'required|string',
            'school_type'  => 'required|string',
            'resume_days'  => 'nullable|integer|min:0',
        ]);

        $director = Director::create([
            'name'         => $validated['name'],
            'email'        => $validated['email'],
            'password'         => Hash::make($validated['password']),
            'profile_img'  => $validated['profile_img'] ?? null,
            'school_name'  => $validated['school_name'],
            'school_code'  => $validated['school_code'],
            'school_email' => $validated['school_email'],
            'school_tel'   => $validated['school_tel'],
            'school_type'  => $validated['school_type'],
            'resume_days'  => $validated['resume_days'] ?? 0,
        ]);

        return response()->json(['message' => 'Director registrado correctamente', 'director' => $director], 201);
    }
}
