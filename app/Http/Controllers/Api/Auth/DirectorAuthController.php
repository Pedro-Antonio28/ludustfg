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
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:directors,email',
            'password' => 'required|string|min:6|confirmed',
            'profile_img' => 'nullable|string|max:255',
            'school_name' => 'required|string|max:255',
            'school_code' => 'required|string|max:255',
            'school_email' => 'required|email|unique:directors,school_email',
            'school_tel' => 'required|string|max:20',
            'school_type' => 'required|in:Público,Privado',
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico no es válido.',
            'email.unique' => 'Este correo ya está registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'school_name.required' => 'El nombre del centro es obligatorio.',
            'school_code.required' => 'El código del centro es obligatorio.',
            'school_email.required' => 'El correo del centro es obligatorio.',
            'school_email.email' => 'El correo del centro no es válido.',
            'school_email.unique' => 'Este correo del centro ya está registrado.',
            'school_tel.required' => 'El teléfono del centro es obligatorio.',
            'school_type.required' => 'El tipo de centro es obligatorio.',
            'school_type.in' => 'El tipo de centro debe ser Público o Privado.',
        ]);

        $director = Director::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'profile_img' => $validated['profile_img'] ?? null,
            'school_name' => $validated['school_name'],
            'school_code' => $validated['school_code'],
            'school_email' => $validated['school_email'],
            'school_tel' => $validated['school_tel'],
            'school_type' => $validated['school_type'],
        ]);

        $token = $director->createToken('director_token')->plainTextToken;

        return response()->json([
            'user' => $director,
            'token' => $token,
            'role' => 'director',
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ], [
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico no es válido.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        $director = Director::where('email', $credentials['email'])->first();

        if (! $director || ! Hash::check($credentials['password'], $director->password)) {
            return response()->json([
                'message' => 'Las credenciales no son válidas.',
            ], 401);
        }

        $token = $director->createToken('director_token')->plainTextToken;

        return response()->json([
            'user' => $director,
            'token' => $token,
            'role' => 'director',
        ]);
    }
}
