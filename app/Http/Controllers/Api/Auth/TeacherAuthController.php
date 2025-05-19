<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TeacherAuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:teacher,email',
            'password' => 'required|string|min:6|confirmed',
            'profile_img' => 'nullable|string|max:255',
        ],
            [
                'name.required' => 'El nombre es obligatorio.',
                'email.required' => 'El correo electrónico es obligatorio.',
                'email.email' => 'El correo electrónico no es válido.',
                'email.unique' => 'Este correo ya está registrado.',
                'password.required' => 'La contraseña es obligatoria.',
                'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
                'password.confirmed' => 'Las contraseñas no coinciden.',
            ]
        );

        $teacher = Teacher::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'profile_img' => $validated['profile_img'] ?? null,
        ]);

        $token = $teacher->createToken('teacher_token')->plainTextToken;

        return response()->json([
            'user' => $teacher,
            'token' => $token,
            'role' => 'teacher',
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ],
            [
                'email.required' => 'El correo electrónico es obligatorio.',
                'email.email' => 'El correo electrónico no es válido.',
                'password.required' => 'La contraseña es obligatoria.',
            ]);

        $teacher = Teacher::where('email', $credentials['email'])->first();

        if (! $teacher || ! Hash::check($credentials['password'], $teacher->password)) {
            return response()->json([
                'message' => 'Las credenciales no son válidas.',
            ], 401);
        }

        $token = $teacher->createToken('teacher_token')->plainTextToken;

        return response()->json([
            'user' => $teacher,
            'token' => $token,
            'role' => 'teacher',
        ]);
    }
}
