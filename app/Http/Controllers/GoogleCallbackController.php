<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Services\AuditoriaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class GoogleCallbackController extends Controller
{
    public function redirect(): RedirectResponse|JsonResponse
    {
        if (empty(config('services.google.client_id'))) {
            return response()->json(['message' => 'Autenticación con Google aún no está habilitada.'], 503);
        }

        return Socialite::driver('google')
            ->with(['hd' => 'unamba.edu.pe', 'prompt' => 'select_account'])
            ->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            $email = strtolower((string) $googleUser->email);
            $googleId = (string) $googleUser->id;

            if (! str_ends_with($email, '@unamba.edu.pe') || ! filter_var($googleUser->user['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                return redirect(config('app.frontend_url').'/login?error=dominio_invalido');
            }

            $usuario = Usuario::where('email', $email)->first();

            if (! $usuario || ! $usuario->activo) {
                return redirect(config('app.frontend_url').'/login?error=acceso_denegado');
            }

            if (! empty($usuario->google_sub) && $usuario->google_sub !== $googleId) {
                return redirect(config('app.frontend_url').'/login?error=acceso_denegado');
            }

            if (empty($usuario->google_sub)) {
                $usuario->update(['google_sub' => $googleId]);
            }

            $usuario->update(['ultimo_login_at' => now()]);

            app(AuditoriaService::class)->registrar('login.google', [
                'entidad' => 'usuarios',
                'entidad_id' => $usuario->id,
                'despues' => ['email' => $usuario->email],
                'usuario_id' => $usuario->id,
            ]);

            Auth::guard('web')->login($usuario);
            $request->session()->regenerate();

            return redirect(config('app.frontend_url').'/');

        } catch (\Exception $e) {
            Log::error('Google Login Error: '.$e->getMessage());

            return redirect(config('app.frontend_url').'/login?error=autenticacion_fallida');
        }
    }
}
