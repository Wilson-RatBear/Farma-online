<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar si el usuario está autenticado
        if (!auth()->check()) {
            return response()->json(['error' => 'No autenticado'], 401);
        }

        // Verificar si el usuario es administrador
        if (!auth()->user()->is_admin) {
            return response()->json(['error' => 'No autorizado. Se requieren permisos de administrador'], 403);
        }

        return $next($request);
    }
}