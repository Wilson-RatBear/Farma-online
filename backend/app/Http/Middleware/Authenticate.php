<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Authenticate
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$guards): Response
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'No autenticado'], 401);
        }

        return $next($request);
    }
}