<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Error del servidor | Camaleón</title>
    @if(!app()->runningUnitTests())
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="min-h-screen bg-gradient-to-b from-slate-900 to-slate-800 flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white/10 backdrop-blur rounded-2xl shadow-xl p-8 text-center text-white">
        <p class="text-7xl font-bold text-pink-300 mb-2">500</p>
        <h1 class="text-2xl font-semibold mb-3">Error del servidor</h1>
        <p class="text-slate-300 mb-6">
            Algo salió mal. Por favor inténtalo de nuevo en unos momentos.
        </p>
        <a href="{{ url('/') }}"
           class="inline-block bg-pink-500 hover:bg-pink-400 text-white font-medium px-6 py-2 rounded-lg transition">
            Volver al inicio
        </a>
    </div>
</body>
</html>
