<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Services\AuditoriaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class GoogleCallbackController extends Controller
{
    public function redirect()
    {
        if (empty(config('services.google.client_id'))) {
            return response()->json(['message' => 'Autenticación con Google aún no está habilitada.'], 503);
        }

        // Simulador de Google para entorno local (evita configurar OAuth real en desarrollo)
        if (app()->environment('local')) {
            return redirect('/api/auth/google/callback?simulated_email=ksegundo@unamba.edu.pe');
        }

        return Socialite::driver('google')
            ->with(['hd' => 'unamba.edu.pe'])
            ->redirect();
    }

    public function callback(Request $request)
    {
        try {
            if (app()->environment('local') && $request->has('simulated_email')) {
                $email = $request->get('simulated_email');
                $googleId = 'simulated_'.rand(1000, 9999);
            } else {
                $googleUser = Socialite::driver('google')->user();
                $email = $googleUser->email;
                $googleId = $googleUser->id;
            }

            if (! str_ends_with($email, '@unamba.edu.pe')) {
                return redirect(config('app.frontend_url').'/login?error=dominio_invalido');
            }

            $usuario = Usuario::where('email', $email)->first();

            // Si es entorno local y el usuario no existe, lo creamos para que pueda probar
            if (app()->environment('local') && ! $usuario) {
                $usuario = Usuario::create([
                    'dni' => '99999999',
                    'nombres' => 'Kely',
                    'apellidos' => 'Segundo (Simulado)',
                    'email' => $email,
                    'rol_id' => 1,
                    'activo' => true,
                ]);
            }

            if (! $usuario || ! $usuario->activo) {
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

            Auth::login($usuario);

            return redirect(config('app.frontend_url').'/');

        } catch (\Exception $e) {
            Log::error('Google Login Error: '.$e->getMessage());

            return redirect(config('app.frontend_url').'/login?error=autenticacion_fallida');
        }
    }
}
