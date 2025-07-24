<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // Registro de usuarios
    public function register(Request $request)
{
    try {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'nullable|string|in:admin,usuario', // rol opcional pero limitado
        ], [
            'email.unique' => 'El correo electrónico ya está en uso.',
            'role.in' => 'El rol debe ser "admin" o "usuario".'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        // 🔹 Asignar rol dinámicamente, por defecto "usuario"
        $role = $request->input('role', 'usuario');
        $user->assignRole($role);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    } catch (\Throwable $e) {
        return response()->json([
            'error' => 'Error en el registro',
            'message' => $e->getMessage()
        ], 500);
    }
}

    // Inicio de sesión
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json(['error' => 'Credenciales inválidas'], 401);
        }

        $user = Auth::user();
        /** @var \App\Models\User $user */
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    // Cierre de sesión
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión cerrada correctamente']);
    }
    // Validar token
    public function validateToken(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
            'message' => 'Token válido'
        ]);
    }
}
