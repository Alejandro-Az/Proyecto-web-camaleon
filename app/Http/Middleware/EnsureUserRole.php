<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware para proteger rutas por rol (admin/client) usando el usuario autenticado.
 *
 * Uso:
 *   ->middleware('role:admin')
 *   ->middleware('role:client')
 *   ->middleware('role:admin,client')
 */
class EnsureUserRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if (! empty($roles) && ! in_array((string) $user->role, $roles, true)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        return $next($request);
    }
}
