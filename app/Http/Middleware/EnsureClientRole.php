<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureClientRole
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user('client');

        if (!$user) {
            return redirect()->to('/panel/login');
        }

        if (($user->role ?? null) !== 'client') {
            abort(403);
        }

        return $next($request);
    }
}
