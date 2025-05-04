<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StudentAuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:students,email',
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

        $student = Student::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'profile_img' => $validated['profile_img'] ?? null,
        ]);

        $token = $student->createToken('student_token')->plainTextToken;

        return response()->json([
            'student' => $student,
            'token'   => $token,
        ], 201);
    }

    public function login(Request $request)
{
    $credentials = $request->validate([
        'email'    => 'required|string|email',
        'password' => 'required|string',
    ],
    [
        'email.required' => 'El correo electrónico es obligatorio.',
        'email.email'    => 'El correo electrónico no es válido.',
        'password.required' => 'La contraseña es obligatoria.',
    ]);

    $student = Student::where('email', $credentials['email'])->first();

    if (!$student || !Hash::check($credentials['password'], $student->password)) {
        return response()->json([
            'message' => 'Las credenciales no son válidas.',
        ], 401);
    }

    $token = $student->createToken('student_token')->plainTextToken;

    return response()->json([
        'student' => $student,
        'token'   => $token,
    ]);
}

}
