<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('client.auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required'    => 'El correo es requerido.',
            'email.email'       => 'El correo no tiene formato válido.',
            'password.required' => 'La contraseña es requerida.',
        ]);

        $credentials = [
            'email'    => $data['email'],
            'password' => $data['password'],
            'role'     => 'client',
        ];

        if (Auth::guard('client')->attempt($credentials, true)) {
            $request->session()->regenerate();

            $defaultUrl = \Illuminate\Support\Facades\Route::has('client.events.index')
                ? route('client.events.index')
                : '/panel/eventos';

            return redirect()->intended($defaultUrl);
        }

        return back()
            ->withErrors(['email' => 'Credenciales inválidas.'])
            ->onlyInput('email');
    }


    public function logout(Request $request)
    {
        Auth::guard('client')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/panel/login');
    }
}
