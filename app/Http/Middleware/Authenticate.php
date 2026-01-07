<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class Authenticate extends Middleware
{

    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            return null;
        }

        if ($request->is('panel') || $request->is('panel/*')) {
            return Route::has('client.login') ? route('client.login') : '/panel/login';
        }

        return Route::has('login') ? route('login') : '/';
    }

}
