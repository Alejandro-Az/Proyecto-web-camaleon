<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $event->name }} | Eventos Camaleón</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-b from-slate-900 to-slate-950 text-slate-100">

    <div class="max-w-5xl mx-auto px-4 py-10 space-y-8">

        {{-- Encabezado del evento --}}
        <header class="bg-slate-800/70 backdrop-blur rounded-3xl shadow-lg px-6 py-8 md:px-10 md:py-10">
            <p class="text-sm uppercase tracking-[0.25em] text-slate-400 mb-3">
                {{ strtoupper($event->type) }}
            </p>

            <h1 class="text-3xl md:text-4xl font-semibold mb-3">
                {{ $event->name }}
            </h1>

            <p class="text-slate-300 mb-1">
                Fecha del evento:
                <span class="font-medium">
                    {{ $event->event_date->translatedFormat('d \\de F \\de Y') }}
                </span>
            </p>

            @if($event->start_time)
                <p class="text-slate-300">
                    Horario:
                    <span class="font-medium">
                        {{ \Carbon\Carbon::createFromTimeString($event->start_time)->format('H:i') }}
                        @if($event->end_time)
                            – {{ \Carbon\Carbon::createFromTimeString($event->end_time)->format('H:i') }} hrs
                        @endif
                    </span>
                </p>
            @endif
        </header>

        {{-- Sección de ubicaciones (misa / recepción) --}}
        @if($event->locations->count())
            <section class="bg-slate-800/60 rounded-3xl p-6 md:p-8 shadow">
                <h2 class="text-xl font-semibold mb-4">Ubicación</h2>

                <div class="space-y-6">
                    @foreach($event->locations as $location)
                        <div class="border border-slate-700 rounded-2xl p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                            <div>
                                <p class="text-sm uppercase tracking-wide text-slate-400">
                                    @switch($location->type)
                                        @case('ceremony') Ceremonia @break
                                        @case('reception') Recepción @break
                                        @default Lugar
                                    @endswitch
                                </p>
                                <p class="text-lg font-medium">{{ $location->name }}</p>
                                @if($location->address)
                                    <p class="text-sm text-slate-300 mt-1">
                                        {{ $location->address }}
                                    </p>
                                @endif
                            </div>

                            @if($location->maps_url)
                                <a href="{{ $location->maps_url }}"
                                   target="_blank"
                                   class="inline-flex items-center justify-center px-4 py-2 rounded-full bg-pink-500 hover:bg-pink-400 text-sm font-semibold shadow">
                                    Abrir en Google Maps
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Galería de fotos (si el módulo está activado) --}}
        @if(data_get($event->modules, 'gallery'))
            @include('events.modules.gallery', [
                'event'         => $event,
                'galleryPhotos' => $galleryPhotos ?? collect(),
            ])
        @endif

        {{-- Módulo RSVP --}}
        @if(data_get($event->modules, 'rsvp'))
            @include('events.modules.rsvp', [
                'event'        => $event,
                'guest'        => $guest ?? null,
                'rsvpEditMode' => $rsvpEditMode ?? false,
            ])
        @endif

        {{-- Módulo: Lista pública de asistentes --}}
        @if(data_get($event->modules, 'public_attendance_list'))
            @include('events.modules.attendance-list', [
                'event'           => $event,
                'confirmedGuests' => $confirmedGuests ?? collect(),
            ])
        @endif

        {{-- Módulo: Canciones y votos --}}
        @if(data_get($event->modules, 'songs'))
            @include('events.modules.songs', [
                'event'                     => $event,
                'guest'                     => $guest ?? null,
                'guestSongSuggestionsCount' => $guestSongSuggestionsCount ?? null,
                'guestVotesCount'           => $guestVotesCount ?? null,
                'votedSongIds'              => $votedSongIds ?? [],
            ])
        @endif

        {{-- Placeholder para otros módulos futuros --}}
        <section class="bg-slate-800/40 rounded-3xl p-6 md:p-8 border border-dashed border-slate-700">
            <h2 class="text-xl font-semibold mb-2">Módulos del evento</h2>
            <p class="text-sm text-slate-300">
                Aquí más adelante vamos a ir mostrando:
                galería de fotos, lista de canciones sugeridas, votos,
                código de vestimenta, regalos, subida de fotos de invitados, etc.,
                en función de los módulos activados en
                <code class="text-xs bg-slate-900/70 px-1 py-0.5 rounded">events.modules</code>.
            </p>
        </section>

    </div>

</body>
</html>
