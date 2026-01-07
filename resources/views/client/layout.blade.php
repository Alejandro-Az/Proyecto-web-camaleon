<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel Cliente') | Camaleón</title>

    @if(!app()->runningUnitTests())
        @vite(['resources/css/app.css','resources/js/app.js'])
    @endif
</head>
<body class="min-h-screen bg-gradient-to-b from-slate-900 to-slate-950 text-slate-100">
    <div class="max-w-6xl mx-auto px-4 py-8 space-y-6">

        @php
            $clientName = optional(auth('client')->user())->name ?? 'Cliente';

            $eventsUrl = \Illuminate\Support\Facades\Route::has('client.events.index')
                ? route('client.events.index')
                : url('/panel/eventos');

            $logoutUrl = \Illuminate\Support\Facades\Route::has('client.logout')
                ? route('client.logout')
                : url('/panel/logout');
        @endphp

        <header class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold">Panel del cliente</h1>
                <p class="text-slate-300 text-sm">{{ $clientName }}</p>
            </div>

            <nav class="flex flex-wrap gap-2">
                <a href="{{ $eventsUrl }}"
                   class="px-4 py-2 rounded-full bg-slate-800 hover:bg-slate-700 text-sm font-semibold">
                    Mis eventos
                </a>

                <form method="POST" action="{{ $logoutUrl }}">
                    @csrf
                    <button type="submit"
                            class="px-4 py-2 rounded-full bg-pink-500 hover:bg-pink-400 text-sm font-semibold shadow">
                        Salir
                    </button>
                </form>
            </nav>
        </header>

        @if(session('status'))
            <div class="rounded-2xl bg-emerald-500/15 border border-emerald-500/30 px-4 py-3 text-emerald-200">
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="rounded-2xl bg-red-500/15 border border-red-500/30 px-4 py-3 text-red-200">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <main>
            @yield('content')
        </main>

    </div>
</body>
</html>
