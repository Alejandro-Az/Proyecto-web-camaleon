<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login | Panel Cliente</title>

    @if(!app()->runningUnitTests())
        @vite(['resources/css/app.css','resources/js/app.js'])
    @endif
</head>
<body class="min-h-screen bg-gradient-to-b from-slate-900 to-slate-950 text-slate-100 flex items-center">
    <div class="max-w-md w-full mx-auto px-4">
        <div class="bg-slate-800/60 border border-slate-700 rounded-3xl p-6 shadow">
            <h1 class="text-2xl font-semibold mb-2">Panel del cliente</h1>
            <p class="text-slate-300 text-sm mb-6">Inicie sesión para administrar sus eventos.</p>

            @if($errors->any())
                <div class="rounded-2xl bg-red-500/15 border border-red-500/30 px-4 py-3 text-red-200 mb-4">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('client.login.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="text-sm text-slate-300">Correo</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="mt-1 w-full rounded-2xl bg-slate-900/60 border border-slate-700 px-4 py-3 outline-none focus:ring-2 focus:ring-pink-500"
                           placeholder="client.demo@camaleon.test" required>
                </div>

                <div>
                    <label class="text-sm text-slate-300">Contraseña</label>
                    <input type="password" name="password"
                           class="mt-1 w-full rounded-2xl bg-slate-900/60 border border-slate-700 px-4 py-3 outline-none focus:ring-2 focus:ring-pink-500"
                           placeholder="password" required>
                </div>

                <button type="submit"
                        class="w-full rounded-2xl bg-pink-500 hover:bg-pink-400 px-4 py-3 font-semibold shadow">
                    Entrar
                </button>

                <p class="text-xs text-slate-400">
                    Demo: <span class="font-mono">client.demo@camaleon.test</span> / <span class="font-mono">password</span>
                </p>
            </form>
        </div>
    </div>
</body>
</html>
