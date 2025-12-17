<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $event->name }} | Eventos Camaleón</title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
        'resources/js/guest-photos.js',
    ])
</head>
<body class="min-h-screen bg-gradient-to-b from-slate-900 to-slate-950 text-slate-100">

    <div class="max-w-5xl mx-auto px-4 py-10 space-y-8">

        @php
            /** @var \App\Models\Event $event */

            $heroPhoto = $heroPhoto
                ?? $event->photos
                    ->where('type', \App\Models\EventPhoto::TYPE_HERO)
                    ->where('status', \App\Models\EventPhoto::STATUS_APPROVED)
                    ->sortBy('display_order')
                    ->sortBy('id')
                    ->first();
        @endphp

        <header class="relative rounded-3xl shadow-lg overflow-hidden bg-slate-800/70 backdrop-blur">
            @if($heroPhoto)
                <div class="absolute inset-0">
                    <img
                        src="{{ asset('storage/' . $heroPhoto->file_path) }}"
                        alt="{{ $heroPhoto->caption ?: 'Foto de portada del evento' }}"
                        class="w-full h-full object-cover"
                    >
                    <div class="absolute inset-0 bg-gradient-to-b from-slate-950/60 via-slate-950/40 to-slate-950/90"></div>
                </div>
            @endif

            <div class="relative px-6 py-8 md:px-10 md:py-10">
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

                @if($heroPhoto && $heroPhoto->caption)
                    <p class="text-sm text-slate-200 mt-3">
                        {{ $heroPhoto->caption }}
                    </p>
                @endif
            </div>
        </header>

        {{-- Módulo: Cuenta regresiva --}}
        @if(data_get($event->modules, 'countdown'))
            @include('events.modules.countdown', ['event' => $event])
        @endif

        {{-- Módulo: Código de vestimenta --}}
        @if(data_get($event->modules, 'dress_code') && $event->dressCodes->count())
            @include('events.modules.dress-code', [
                'event'      => $event,
                'dressCodes' => $event->dressCodes,
            ])
        @endif

        {{-- Módulo: Itinerario / schedule --}}
        @if(data_get($event->modules, 'schedule') && $event->schedules->count())
            @include('events.modules.schedule', [
                'event'     => $event,
                'schedules' => $event->schedules,
            ])
        @endif

        {{-- Sección de ubicaciones (FIX: respeta modules.map) --}}
        @if(data_get($event->modules, 'map') && $event->locations->count())
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

        {{-- Galería de fotos --}}
        @if(data_get($event->modules, 'gallery'))
            @include('events.modules.gallery', [
                'event'         => $event,
                'galleryPhotos' => $galleryPhotos ?? collect(),
            ])
        @endif

        {{-- Módulo: Fotos de invitados --}}
        @if(data_get($event->modules, 'guest_photos_upload'))
            @include('events.modules.guest-photos', [
                'event'       => $event,
                'guest'       => $guest ?? null,
                'guestPhotos' => $guestPhotos ?? collect(),
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

        {{-- Módulo: Mesa de regalos --}}
        @if(data_get($event->modules, 'gifts'))
            @include('events.modules.gifts', [
                'event'                   => $event,
                'gifts'                   => $gifts ?? collect(),
                'guest'                   => $guest ?? null,
                'guestGiftClaimsByGiftId' => $guestGiftClaimsByGiftId ?? collect(),
            ])
        @endif

        {{-- Módulo: Frases románticas / del evento --}}
        @if(data_get($event->modules, 'romantic_phrases') && $event->romanticPhrases->count())
            @include('events.modules.romantic-phrases', [
                'event'   => $event,
                'phrases' => $event->romanticPhrases,
            ])
        @endif

        {{-- Placeholder --}}
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
