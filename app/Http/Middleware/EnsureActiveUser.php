<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveUser
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->activo === false) {
            return response()->json(['message' => 'Usuario inactivo. Contacte al administrador del VRIN.'], 401);
        }

        return $next($request);
    }
}
