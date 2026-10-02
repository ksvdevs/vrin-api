<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\Usuario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $usuario = Usuario::with('rolRef')
            ->where('email', $request->input('email'))
            ->first();

        if ($usuario === null || $usuario->password_hash === null || ! Hash::check($request->input('password'), $usuario->password_hash)) {
            return response()->json([
                'message' => 'Credenciales incorrectas.',
                'errors' => ['email' => ['Credenciales incorrectas.']],
            ], 422);
        }

        if (! $usuario->activo) {
            return response()->json(['message' => 'Usuario inactivo. Contacte al administrador del VRIN.'], 403);
        }

        Auth::login($usuario);
        $request->session()->regenerate();

        $usuario->ultimo_login_at = now();
        $usuario->save();

        return response()->json($usuario->only(
            'id', 'dni', 'nombres', 'apellidos', 'nombre', 'email',
            'rol', 'rol_codigo', 'rol_id', 'activo', 'ultimo_login_at',
        ));
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Sesión cerrada.']);
    }
}
