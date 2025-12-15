@php
    $maxPhotosPerGuest = (int) data_get($event->settings ?? [], 'guest_photos_max_per_guest', 5);
    $hasServerErrors = $errors->has('invitation_code') || $errors->has('photo');
@endphp

<section class="bg-slate-800/60 rounded-3xl p-6 md:p-8 shadow space-y-6">

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
        <h2 class="text-xl font-semibold mb-2">
            Fotos de invitados
        </h2>
        <p class="text-sm text-slate-300">
            Comparta sus mejores momentos del evento. Puede subir hasta
            <span class="font-medium">{{ $maxPhotosPerGuest }}</span>
            fotos por invitación.
        </p>
    </header>

    {{-- Formulario de subida --}}
    <div class="bg-slate-900/40 rounded-2xl p-4 border border-slate-700">
        @if($guest)
            <p class="text-sm text-slate-300 mb-3">
                Está subiendo fotos como
                <span class="font-semibold">{{ $guest->name }}</span>.
            </p>
        @else
            <p class="text-sm text-slate-300 mb-3">
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
                    <label class="block text-xs font-semibold text-slate-300 mb-1">
                        Código de invitación
                    </label>
                    <input
                        type="text"
                        name="invitation_code"
                        required
                        value="{{ old('invitation_code') }}"
                        class="w-full rounded-xl bg-slate-900/80 border border-slate-700 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-pink-500"
                        placeholder="Ej. DEMO1234"
                    >
                </div>
            @endif

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">
                    Foto
                </label>
                <input
                    type="file"
                    name="photo"
                    accept="image/*"
                    required
                    class="w-full text-sm text-slate-200 file:mr-3 file:py-2 file:px-3 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-pink-500 file:text-white hover:file:bg-pink-400"
                >
                <p class="text-xs text-slate-400 mt-1">
                    Tamaño máximo aproximado: 4 MB.
                </p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">
                    Descripción (opcional)
                </label>
                <input
                    type="text"
                    name="caption"
                    maxlength="255"
                    value="{{ old('caption') }}"
                    class="w-full rounded-xl bg-slate-900/80 border border-slate-700 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-pink-500"
                    placeholder="Ej. Selfie en la pista de baile"
                >
            </div>

            <button
                type="submit"
                class="inline-flex items-center justify-center px-4 py-2 rounded-full bg-pink-500 hover:bg-pink-400 text-sm font-semibold shadow"
            >
                Subir foto
            </button>
        </form>
    </div>

    {{-- Grid de fotos aprobadas --}}
    @if($guestPhotos->isEmpty())
        <p class="text-sm text-slate-400" data-guest-photos-empty>
            Aún no hay fotos de invitados aprobadas para mostrar.
        </p>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3" data-guest-photos-grid></div>
    @else
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3" data-guest-photos-grid>
            @foreach($guestPhotos as $photo)
                @php
                    $url = Storage::disk('public')->url($photo->file_path);
                @endphp
                <figure class="relative rounded-2xl overflow-hidden bg-slate-900/60 border border-slate-700">
                    <img
                        src="{{ $url }}"
                        alt="{{ $photo->caption ?? 'Foto de invitado' }}"
                        class="w-full h-40 object-cover"
                        loading="lazy"
                    >
                    @if($photo->caption)
                        <figcaption class="absolute inset-x-0 bottom-0 bg-slate-900/70 px-2 py-1 text-[11px] text-slate-100">
                            {{ $photo->caption }}
                        </figcaption>
                    @endif
                </figure>
            @endforeach
        </div>
    @endif
</section>
