@extends('client.layout')

@section('title', 'Administrar evento')

@section('content')
    <div class="mb-4">
        <a href="{{ route('client.events.index') }}" class="inline-flex items-center gap-2 text-[var(--ink-muted)] hover:text-[var(--ink)] transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Volver a mis eventos
        </a>
    </div>

    <div class="space-y-6">
        {{-- Header & Hero --}}
        <div class="relative rounded-3xl overflow-hidden bg-[var(--surface-alt)] shadow-xl group">
            <div class="absolute inset-0">
                @if($event->cover_image_path)
                    <img src="{{ Storage::url($event->cover_image_path) }}" alt="Hero" class="w-full h-full object-cover opacity-60">
                @else
                    <div class="w-full h-full bg-[var(--surface)] opacity-60"></div>
                @endif
                <div class="absolute inset-0 bg-[var(--surface)]"></div>
            </div>

            <div class="relative p-6 md:p-10 flex flex-col md:flex-row md:items-end md:justify-between gap-6">
                <div>
                    <h1 class="text-3xl md:text-4xl font-bold text-[var(--ink)]">{{ $event->name }}</h1>
                    <div class="flex items-center gap-3 mt-2 text-[var(--ink-soft)]">
                        @if($event->event_date)
                            <span class="flex items-center gap-1.5 bg-[var(--surface)] border border-[var(--rule)] text-[var(--ink)] px-3 py-1 rounded-full text-sm backdrop-blur-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                {{ $event->event_date->translatedFormat('d M Y') }}
                            </span>
                        @endif
                        <span class="flex items-center gap-1.5 bg-[var(--surface)] border border-[var(--rule)] text-[var(--ink)] px-3 py-1 rounded-full text-sm backdrop-blur-sm uppercase tracking-wider">
                            {{ $event->type ?? 'Evento' }}
                        </span>
                    </div>
                </div>

                <div class="flex flex-col gap-2 w-full md:w-auto">
                    <form action="{{ route('client.events.hero', $event) }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-2">
                        @csrf
                        <label class="inline-flex items-center justify-center px-4 py-2 rounded-full border border-[var(--rule)] text-[var(--ink)] bg-[var(--surface)] hover:bg-[var(--surface-alt)] text-sm font-semibold transition-all text-xs py-2 px-3 cursor-pointer text-center">
                            <span>📷 Cambiar Portada</span>
                            <cml-input border border-[var(--rule)] rounded-xl bg-[var(--surface)] focus:ring-2 focus:ring-[var(--accent)] type="file" name="hero_image" class="hidden" onchange="this.form.submit()">
                        </label>
                    </form>
                    
                    @if($event->slug)
                        <a href="{{ url('/eventos/' . $event->slug) }}" target="_blank" class="inline-flex items-center justify-center px-4 py-2 rounded-full bg-[var(--accent)] text-white hover:brightness-110 text-sm font-semibold transition-all text-center">
                            Ver Página Pública ↗
                        </a>
                    @endif
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 rounded-xl bg-green-500/10 border border-green-500/20 text-green-400">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Column Left: General Info --}}
            <div class="lg:col-span-1 space-y-6">
                <form action="{{ route('client.events.update', $event) }}" method="POST" class="bg-[var(--surface)] border border-[var(--rule)] rounded-2xl shadow-sm p-6 space-y-4">
                    @csrf
                    @method('PUT')
                    
                    <h3 class="text-lg font-semibold text-[var(--ink)] mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-[var(--accent)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Información General
                    </h3>

                    <div>
                        <label class="block text-sm font-medium text-[var(--ink-muted)] mb-1">Nombre del Evento</label>
                        <cml-input border border-[var(--rule)] rounded-xl bg-[var(--surface)] focus:ring-2 focus:ring-[var(--accent)] type="text" name="name" value="{{ old('name', $event->name) }}" class="cml-input border border-[var(--rule)] rounded-xl bg-[var(--surface)] focus:ring-2 focus:ring-[var(--accent)] w-full">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[var(--ink-muted)] mb-1">Fecha</label>
                        <cml-input border border-[var(--rule)] rounded-xl bg-[var(--surface)] focus:ring-2 focus:ring-[var(--accent)] type="date" name="event_date" value="{{ old('event_date', $event->event_date?->format('Y-m-d')) }}" class="cml-input border border-[var(--rule)] rounded-xl bg-[var(--surface)] focus:ring-2 focus:ring-[var(--accent)] w-full">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-[var(--ink-muted)] mb-1">Inicio</label>
                            <cml-input border border-[var(--rule)] rounded-xl bg-[var(--surface)] focus:ring-2 focus:ring-[var(--accent)] type="time" name="start_time" value="{{ old('start_time', $event->start_time ? \Carbon\Carbon::parse($event->start_time)->format('H:i') : '') }}" class="cml-input border border-[var(--rule)] rounded-xl bg-[var(--surface)] focus:ring-2 focus:ring-[var(--accent)] w-full">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-[var(--ink-muted)] mb-1">Fin</label>
                            <cml-input border border-[var(--rule)] rounded-xl bg-[var(--surface)] focus:ring-2 focus:ring-[var(--accent)] type="time" name="end_time" value="{{ old('end_time', $event->end_time ? \Carbon\Carbon::parse($event->end_time)->format('H:i') : '') }}" class="cml-input border border-[var(--rule)] rounded-xl bg-[var(--surface)] focus:ring-2 focus:ring-[var(--accent)] w-full">
                        </div>
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="inline-flex items-center justify-center px-4 py-2 rounded-full bg-[var(--accent)] text-white hover:brightness-110 text-sm font-semibold transition-all w-full justify-center">Guardar Cambios</button>
                    </div>
                </form>
            </div>

            {{-- Column Right: Modules & Settings --}}
            <div class="lg:col-span-2 space-y-6">
                <form action="{{ route('client.events.update', $event) }}" method="POST">
                    @csrf
                    @method('PUT')

                    {{-- Theme & Palette Selection --}}
                    <div class="bg-[var(--surface)] border border-[var(--rule)] rounded-2xl shadow-sm p-6 mb-6">
                        <h3 class="text-lg font-semibold text-[var(--ink)] mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-[var(--accent)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><circle cx="7.5" cy="10.5" r="1"/><circle cx="12" cy="7.5" r="1"/><circle cx="16.5" cy="10.5" r="1"/><path d="M12 21a3 3 0 010-6 2 2 0 002-2 2 2 0 012-2h2"/></svg>
                            Apariencia y Tema
                        </h3>
                        
                        <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                            @php
                                $palettes = config('themes.palettes', []);
                                $activePalette = $event->getSetting('theme_palette', 'tuscan');
                            @endphp
                            @foreach($palettes as $key => $palette)
                                <label class="cursor-pointer relative group">
                                    <cml-input border border-[var(--rule)] rounded-xl bg-[var(--surface)] focus:ring-2 focus:ring-[var(--accent)] type="radio" name="settings[theme_palette]" value="{{ $key }}" class="peer sr-only" {{ $activePalette === $key ? 'checked' : '' }}>
                                    <div class="rounded-xl border-2 border-[var(--rule)] bg-[var(--surface-alt)] p-3 peer-checked:border-pink-500 peer-checked:bg-[var(--surface-alt)] transition-all hover:border-slate-500">
                                        <div class="flex h-10 w-full overflow-hidden rounded border border-slate-900 mb-2">
                                            <div class="flex-1" style="background-color: {{ $palette['bg'] }}"></div>
                                            <div class="flex-1" style="background-color: {{ $palette['accent'] }}"></div>
                                            <div class="flex-1" style="background-color: {{ $palette['ink'] }}"></div>
                                        </div>
                                        <div class="text-xs font-semibold text-[var(--ink)] truncate">{{ $palette['name'] }}</div>
                                        <div class="text-[10px] text-[var(--ink-muted)] truncate mt-0.5">{{ explode('·', $palette['subtitle'])[0] ?? '' }}</div>
                                    </div>
                                    @if($activePalette === $key)
                                        <div class="absolute -top-2 -right-2 bg-pink-500 text-[var(--ink)] rounded-full p-1 shadow-lg">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                        </div>
                                    @endif
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Modules Grid --}}
                    <div class="bg-[var(--surface)] border border-[var(--rule)] rounded-2xl shadow-sm p-6 mb-6">
                        <h3 class="text-lg font-semibold text-[var(--ink)] mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-[var(--accent)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                            Módulos Activos
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @php
                                $features = [
                                    ['key' => 'rsvp', 'label' => 'Confirmación de Asistencia (RSVP)', 'desc' => 'Permite a tus invitados confirmar si irán.'],
                                    ['key' => 'gifts', 'label' => 'Mesa de Regalos', 'desc' => 'Lista de regalos, sobres de efectivo o Amazon.'],
                                    ['key' => 'songs', 'label' => 'Sugerencias de Canciones', 'desc' => 'Tus invitados votan por la música.'],
                                    ['key' => 'guest_photos_upload', 'label' => 'Fotos de Invitados', 'desc' => 'Recopila fotos tomadas por ellos.'],
                                    ['key' => 'schedule', 'label' => 'Itinerario', 'desc' => 'Cronograma visual del evento.'],
                                    ['key' => 'dress_code', 'label' => 'Código de Vestimenta', 'desc' => 'Ejemplos visuales y descripción.'],
                                    ['key' => 'map', 'label' => 'Mapa y Ubicaciones', 'desc' => 'Links directos a Google Maps/Waze.'],
                                    ['key' => 'countdown', 'label' => 'Cuenta Regresiva', 'desc' => 'Contador hasta el gran día.'],
                                ];
                            @endphp

                            @foreach($features as $feature)
                                @php
                                    $isAllowed = $event->isModuleAvailable($feature['key']);
                                    $isEnabled = $event->isModuleEnabled($feature['key']);
                                @endphp
                                <div class="relative flex items-start p-4 rounded-xl border {{ $isEnabled ? 'border-[var(--accent)] bg-[var(--surface)]' : 'border-[var(--rule)] bg-[var(--surface-alt)]' }} {{ !$isAllowed ? 'opacity-50 grayscale' : '' }}">
                                    <div class="flex items-center h-5">
                                        {{-- Hidden cml-input border border-[var(--rule)] rounded-xl bg-[var(--surface)] focus:ring-2 focus:ring-[var(--accent)] for false value --}}
                                        <cml-input border border-[var(--rule)] rounded-xl bg-[var(--surface)] focus:ring-2 focus:ring-[var(--accent)] type="hidden" name="modules[{{ $feature['key'] }}]" value="0">
                                        <cml-input border border-[var(--rule)] rounded-xl bg-[var(--surface)] focus:ring-2 focus:ring-[var(--accent)] 
                                            type="checkbox" 
                                            name="modules[{{ $feature['key'] }}]" 
                                            value="1" 
                                            class="w-4 h-4 text-indigo-600 bg-[var(--rule)] border-[var(--rule)] rounded focus:ring-indigo-600 focus:ring-offset-slate-800"
                                            {{ $isEnabled ? 'checked' : '' }}
                                            {{ !$isAllowed ? 'disabled' : '' }}
                                        >
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label class="font-medium text-[var(--ink)]">
                                            {{ $feature['label'] }}
                                            @if(!$isAllowed)
                                                <span class="ml-2 text-xs text-amber-500 border border-amber-500/30 px-1.5 py-0.5 rounded">Premium</span>
                                            @endif
                                        </label>
                                        <p class="text-[var(--ink-muted)] text-xs mt-1">{{ $feature['desc'] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Settings Grid --}}
                    <div class="bg-[var(--surface)] border border-[var(--rule)] rounded-2xl shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-[var(--ink)] mb-4 flex items-center gap-2">
                             <svg class="w-5 h-5 text-[var(--accent)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            Configuración Avanzada
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Gifts Settings --}}
                            <div class="md:col-span-2">
                                <h4 class="text-sm font-semibold text-[var(--ink)] mb-3 border-b border-[var(--rule)] pb-1">Mesa de Regalos</h4>
                                <div class="flex items-center gap-2">
                                    <cml-input border border-[var(--rule)] rounded-xl bg-[var(--surface)] focus:ring-2 focus:ring-[var(--accent)] type="hidden" name="settings[gifts_hide_purchased_from_public]" value="0">
                                    <cml-input border border-[var(--rule)] rounded-xl bg-[var(--surface)] focus:ring-2 focus:ring-[var(--accent)] 
                                        type="checkbox" 
                                        name="settings[gifts_hide_purchased_from_public]" 
                                        value="1" 
                                        class="toggle-checkbox"
                                        {{ $event->getSetting('gifts_hide_purchased_from_public') ? 'checked' : '' }}
                                    >
                                    <span class="text-sm text-[var(--ink-muted)]">Ocultar regalos ya comprados de la vista pública</span>
                                </div>
                            </div>

                            {{-- Songs Settings --}}
                            <div>
                                <h4 class="text-sm font-semibold text-[var(--ink)] mb-3 border-b border-[var(--rule)] pb-1">Música (Playlist)</h4>
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-xs text-[var(--ink-muted)] mb-1">Máx. canciones por invitado</label>
                                        <cml-input border border-[var(--rule)] rounded-xl bg-[var(--surface)] focus:ring-2 focus:ring-[var(--accent)] 
                                            type="number" 
                                            name="settings[playlist_max_songs_per_guest]" 
                                            value="{{ $event->getSetting('playlist_max_songs_per_guest', 1) }}" 
                                            min="0" max="10"
                                            class="cml-input border border-[var(--rule)] rounded-xl bg-[var(--surface)] focus:ring-2 focus:ring-[var(--accent)] w-full md:w-full"
                                        >
                                    </div>
                                    <div>
                                        <label class="block text-xs text-[var(--ink-muted)] mb-1">Máx. votos por invitado</label>
                                        <cml-input border border-[var(--rule)] rounded-xl bg-[var(--surface)] focus:ring-2 focus:ring-[var(--accent)] 
                                            type="number" 
                                            name="settings[playlist_max_votes_per_guest]" 
                                            value="{{ $event->getSetting('playlist_max_votes_per_guest', 3) }}" 
                                            min="0" max="20"
                                            class="cml-input border border-[var(--rule)] rounded-xl bg-[var(--surface)] focus:ring-2 focus:ring-[var(--accent)] w-full md:w-full"
                                        >
                                    </div>
                                </div>
                            </div>

                            {{-- Guest Photos Settings --}}
                            <div>
                                <h4 class="text-sm font-semibold text-[var(--ink)] mb-3 border-b border-[var(--rule)] pb-1">Fotos de Invitados</h4>
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-xs text-[var(--ink-muted)] mb-1">Máx. fotos por invitado</label>
                                        <cml-input border border-[var(--rule)] rounded-xl bg-[var(--surface)] focus:ring-2 focus:ring-[var(--accent)] 
                                            type="number" 
                                            name="settings[guest_photos_max_per_guest]" 
                                            value="{{ $event->getSetting('guest_photos_max_per_guest', 5) }}" 
                                            min="0" max="50"
                                            class="cml-input border border-[var(--rule)] rounded-xl bg-[var(--surface)] focus:ring-2 focus:ring-[var(--accent)] w-full md:w-full"
                                        >
                                    </div>
                                    <div class="flex items-start gap-2 pt-2">
                                        <cml-input border border-[var(--rule)] rounded-xl bg-[var(--surface)] focus:ring-2 focus:ring-[var(--accent)] type="hidden" name="settings[guest_photos_auto_approve]" value="0">
                                        <cml-input border border-[var(--rule)] rounded-xl bg-[var(--surface)] focus:ring-2 focus:ring-[var(--accent)] 
                                            type="checkbox" 
                                            name="settings[guest_photos_auto_approve]" 
                                            value="1" 
                                            class="toggle-checkbox mt-0.5"
                                            {{ $event->getSetting('guest_photos_auto_approve') ? 'checked' : '' }}
                                        >
                                        <div class="text-xs">
                                            <span class="block text-[var(--ink-soft)]">Aprobación automática</span>
                                            <span class="block text-slate-500">Las fotos aparecen sin moderación previa.</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                         <div class="pt-6 border-t border-[var(--rule)] mt-6 md:flex md:justify-end">
                            <button type="submit" class="inline-flex items-center justify-center px-4 py-2 rounded-full bg-[var(--accent)] text-white hover:brightness-110 text-sm font-semibold transition-all w-full md:w-auto px-8">Guardar Configuración y Módulos</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
