<?php

namespace App\Http\Middleware;

use App\Models\Usuario;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Login simulado (D-06): solo en entorno local, autentica como cualquier
 * usuario de `usuarios` vía header X-Dev-User-Id. Se retira en la Fase 11
 * cuando llegue la autenticación real con Google institucional.
 */
class DevRoleAuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->environment('local') && $request->hasHeader('X-Dev-User-Id')) {
            $usuario = Usuario::where('activo', true)->find($request->header('X-Dev-User-Id'));

            if ($usuario) {
                Auth::login($usuario);
            }
        }

        return $next($request);
    }
}
