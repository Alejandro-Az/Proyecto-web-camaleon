<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $event->name }} | Eventos Camaleón</title>

    @if(!app()->runningUnitTests())
        @vite([
            'resources/css/app.css',
            'resources/js/app.js',
            'resources/js/guest-photos.js',
        ])
    @endif
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="cml bg-[var(--bg)] min-h-screen" style="{{ $event->palette_style }}">

    {{-- Sticky-ish Nav --}}
    <div class="flex items-center justify-between px-6 py-4 md:px-14 border-b border-[var(--rule)] bg-[var(--bg)] sticky top-0 z-50">
        <div class="cml-serif text-xl italic text-[var(--ink)]">
            {{ Str::substr($event->name, 0, 1) }}<span class="text-[var(--accent)]">&amp;</span>{{ Str::substr(explode(' ', $event->name)[1] ?? 'M', 0, 1) }}
        </div>
        <div class="hidden md:flex gap-8">
            <a href="#detalles" class="cml-eyebrow text-[var(--accent)] border-b border-[var(--accent)] pb-1 transition-colors">Detalles</a>
            @if($event->isModuleEnabled('rsvp'))<a href="#rsvp" class="cml-eyebrow text-[var(--ink-soft)] hover:text-[var(--accent)] pb-1 transition-colors">RSVP</a>@endif
            @if($event->isModuleEnabled('gifts'))<a href="#gifts" class="cml-eyebrow text-[var(--ink-soft)] hover:text-[var(--accent)] pb-1 transition-colors">Regalos</a>@endif
            @if($event->isModuleEnabled('songs'))<a href="#songs" class="cml-eyebrow text-[var(--ink-soft)] hover:text-[var(--accent)] pb-1 transition-colors">Música</a>@endif
            @if($event->isModuleEnabled('gallery'))<a href="#gallery" class="cml-eyebrow text-[var(--ink-soft)] hover:text-[var(--accent)] pb-1 transition-colors">Galería</a>@endif
        </div>
        @if($event->isModuleEnabled('rsvp'))
            <a href="#rsvp" class="cml-btn cml-btn--accent px-4 py-2 text-xs">RSVP</a>
        @else
            <div></div>
        @endif
    </div>

    {{-- Hero Split --}}
    <div class="grid grid-cols-1 md:grid-cols-[1.05fr_1fr] min-h-[640px]">
        @if($event->cover_image_path)
            <img src="{{ Storage::url($event->cover_image_path) }}" alt="Foto de portada" class="w-full h-full object-cover">
        @elseif($heroPhoto)
            <img src="{{ asset('storage/' . $heroPhoto->file_path) }}" alt="Foto de portada" class="w-full h-full object-cover">
        @else
            <div class="w-full h-full cml-img-ph rounded-none aspect-[4/5] md:aspect-auto">FOTO · PORTADA</div>
        @endif

        <div class="flex flex-col items-center justify-center px-6 py-20 md:px-14 text-center">
            <div class="cml-eyebrow mb-6">{{ $event->type === 'wedding' ? 'Nos casamos' : 'Celebramos' }}</div>
            
            <h1 class="cml-serif text-5xl md:text-6xl text-[var(--ink)] mb-2">
                {{ $event->name }}
            </h1>

            <div class="cml-divider max-w-[130px] mx-auto mt-8 mb-6 text-[var(--accent)]">
                <i data-lucide="leaf" class="w-4 h-4 mx-auto"></i>
            </div>
            
            @if($heroPhoto && $heroPhoto->caption)
                <div class="cml-serif text-xl md:text-2xl italic text-[var(--ink-soft)] max-w-sm mb-10 leading-relaxed">
                    {{ $heroPhoto->caption }}
                </div>
            @endif

            <div class="cml-eyebrow mb-2">{{ $event->event_date->translatedFormat('d \de F \de Y') }}</div>
            <div class="cml-sans text-sm text-[var(--ink-muted)]">
                @if($event->locations->count() > 0)
                    {{ $event->locations->first()->name }} · {{ $event->locations->first()->city ?? 'Ciudad' }}
                @else
                    Lugar del evento
                @endif
            </div>
        </div>
    </div>

    {{-- Details / Map Strip --}}
    @if($event->locations->count() > 0)
        <div id="detalles" class="grid grid-cols-1 md:grid-cols-3 border-t border-b border-[var(--rule)]">
            @foreach($event->locations->take(3) as $index => $loc)
                <div class="p-10 text-center {{ $index < 2 ? 'md:border-r border-b md:border-b-0 border-[var(--rule)]' : '' }}">
                    <div class="text-[var(--accent)] inline-block mb-4">
                        <i data-lucide="map-pin" class="w-7 h-7"></i>
                    </div>
                    <div class="cml-eyebrow mb-2">{{ $loc->type === 'ceremony' ? 'Ceremonia' : ($loc->type === 'reception' ? 'Recepción' : 'Ubicación') }}</div>
                    <div class="cml-serif text-lg text-[var(--ink)]">
                        {{ $loc->name }}
                        @if($loc->maps_url)
                            <a href="{{ $loc->maps_url }}" target="_blank" class="block text-sm text-[var(--accent)] mt-2 hover:underline font-sans">Ver mapa</a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Modules --}}
    @if($event->isModuleEnabled('countdown'))
        @include('events.modules.countdown', ['event' => $event])
    @endif

    @if($event->isModuleEnabled('story') && $event->stories->count())
        @include('events.modules.story', ['event' => $event, 'stories' => $event->stories])
    @endif

    @if($event->isModuleEnabled('schedule') && $event->schedules->count())
        @include('events.modules.schedule', ['event' => $event, 'schedules' => $event->schedules])
    @endif

    @if($event->isModuleEnabled('dress_code') && $event->dressCodes->count())
        @include('events.modules.dress-code', ['event' => $event, 'dressCodes' => $event->dressCodes])
    @endif

    @if($event->isModuleEnabled('rsvp'))
        @include('events.modules.rsvp', ['event' => $event, 'guest' => $guest ?? null, 'rsvpEditMode' => $rsvpEditMode ?? false])
    @endif

    @if($event->isModuleEnabled('gifts'))
        @include('events.modules.gifts', ['event' => $event, 'gifts' => $gifts ?? collect(), 'guest' => $guest ?? null])
    @endif

    @if($event->isModuleEnabled('songs'))
        @include('events.modules.songs', ['event' => $event, 'guest' => $guest ?? null])
    @endif

    @if($event->isModuleEnabled('gallery'))
        @include('events.modules.gallery', ['event' => $event])
    @endif

    @if($event->isModuleEnabled('guest_photos_upload'))
        @include('events.modules.guest-photos', ['event' => $event, 'guest' => $guest ?? null])
    @endif

    {{-- Footer --}}
    <div class="py-16 px-10 text-center border-t border-[var(--rule)] mt-20">
        <h2 class="cml-serif text-3xl italic text-[var(--ink)]">
            {{ Str::substr($event->name, 0, 1) }}<span class="text-[var(--accent)]">&amp;</span>{{ Str::substr(explode(' ', $event->name)[1] ?? 'M', 0, 1) }}
        </h2>
        <div class="cml-eyebrow mt-4 text-[var(--ink-muted)]">{{ $event->event_date->translatedFormat('d \de F \de Y') }}</div>
        <div class="cml-sans text-xs text-[var(--ink-muted)] mt-6 opacity-70 inline-flex items-center gap-2">
            Sitio creado con <i data-lucide="leaf" class="w-3 h-3 text-[var(--accent)]"></i> Camaleón
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>