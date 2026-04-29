<div style="padding: 80px 24px; ">
    <div style="max-width: 1100px; margin: 0 auto;">
        <div style="text-align: center; margin-bottom: 50px;"><div class="cml-eyebrow" style="margin-bottom: 14px;">Tus memorias</div><h2 class="cml-serif text-4xl italic text-[var(--ink)]">Fotos de invitados</h2><div class="cml-divider max-w-[80px] mx-auto mt-6 text-[var(--accent)]"><i data-lucide="leaf" class="w-4 h-4 mx-auto"></i></div></div>
@php
    $maxPhotosPerGuest = (int) data_get($event->settings ?? [], 'guest_photos_max_per_guest', 5);
    $hasServerErrors = $errors->has('invitation_code') || $errors->has('photo');
@endphp



    {{-- Mensaje de éxito (usado por fallback y por JS) --}}
    <div
        data-guest-photos-success
        class="mb-4 rounded-xl bg-emerald-500/10 border border-emerald-500/40 px-3 py-2 text-sm text-emerald-200 {{ session('guest_photo_success') ? '' : 'hidden' }}"
    >
        {{ session('guest_photo_success') ?? '' }}
    </div>

    {{-- Errores (rellenados por fallback o por JS) --}}
    <div
        data-guest-photos-errors
        class="mb-4 rounded-xl bg-red-500/10 border border-red-500/40 px-3 py-2 text-sm text-red-200 {{ $hasServerErrors ? '' : 'hidden' }}"
    >
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->get('invitation_code') as $error)
                <li>{{ $error }}</li>
            @endforeach
            @foreach($errors->get('photo') as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>

    <header>
        
        <p class="text-sm text-[var(--ink-soft)]">
            Comparta sus mejores momentos del evento. Puede subir hasta
            <span class="font-medium">{{ $maxPhotosPerGuest }}</span>
            fotos por invitación.
        </p>
    </header>

    {{-- Formulario de subida --}}
    <div class="bg-[var(--surface)]/40 rounded-2xl p-4 border border-[var(--rule)]">
        @if($guest)
            <p class="text-sm text-[var(--ink-soft)] mb-3">
                Está subiendo fotos como
                <span class="font-semibold">{{ $guest->name }}</span>.
            </p>
        @else
            <p class="text-sm text-[var(--ink-soft)] mb-3">
                Ingrese el código de invitación que recibió para subir sus fotos.
            </p>
        @endif

        <form
            method="POST"
            action="{{ route('events.guest-photos.store', ['slug' => $event->slug]) }}"
            enctype="multipart/form-data"
            class="space-y-3"
            data-guest-photos-form
        >
            @csrf

            @if($guest)
                <input type="hidden" name="invitation_code" value="{{ $guest->invitation_code }}">
            @else
                <div>
                    <label class="block text-xs font-semibold text-[var(--ink-soft)] mb-1">
                        Código de invitación
                    </label>
                    <input
                        type="text"
                        name="invitation_code"
                        required
                        value="{{ old('invitation_code') }}"
                        class="w-full rounded-xl bg-[var(--surface)]/80 border border-[var(--rule)] px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-pink-500"
                        placeholder="Ej. DEMO1234"
                    >
                </div>
            @endif

            <div>
                <label class="block text-xs font-semibold text-[var(--ink-soft)] mb-1">
                    Foto
                </label>
                <input
                    type="file"
                    name="photo"
                    accept="image/*"
                    required
                    class="w-full text-sm text-[var(--ink)] file:mr-3 file:py-2 file:px-3 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-[var(--accent)] text-white file:text-white hover:file:bg-pink-400"
                >
                <p class="text-xs text-[var(--ink-muted)] mt-1">
                    Tamaño máximo aproximado: 4 MB.
                </p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-[var(--ink-soft)] mb-1">
                    Descripción (opcional)
                </label>
                <input
                    type="text"
                    name="caption"
                    maxlength="255"
                    value="{{ old('caption') }}"
                    class="w-full rounded-xl bg-[var(--surface)]/80 border border-[var(--rule)] px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-pink-500"
                    placeholder="Ej. Selfie en la pista de baile"
                >
            </div>

            <button
                type="submit"
                class="inline-flex items-center justify-center px-4 py-2 rounded-full bg-[var(--accent)] text-white hover:brightness-110 text-sm font-semibold "
            >
                Subir foto
            </button>
        </form>
    </div>

    {{-- Grid de fotos aprobadas --}}
    @if($guestPhotos->isEmpty())
        <p class="text-sm text-[var(--ink-muted)]" data-guest-photos-empty>
            Aún no hay fotos de invitados aprobadas para mostrar.
        </p>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3" data-guest-photos-grid></div>
    @else
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3" data-guest-photos-grid>
            @foreach($guestPhotos as $photo)
                @php
                    $url = Storage::disk('public')->url($photo->file_path);
                @endphp
                <figure class="relative rounded-2xl overflow-hidden bg-[var(--surface)]/60 border border-[var(--rule)]">
                    <img
                        src="{{ $url }}"
                        alt="{{ $photo->caption ?? 'Foto de invitado' }}"
                        class="w-full h-40 object-cover"
                        loading="lazy"
                    >
                    @if($photo->caption)
                        <figcaption class="absolute inset-x-0 bottom-0 bg-[var(--surface)]/70 px-2 py-1 text-[11px] text-[var(--ink)]">
                            {{ $photo->caption }}
                        </figcaption>
                    @endif
                </figure>
            @endforeach
        </div>
    @endif

    </div>
</div>
