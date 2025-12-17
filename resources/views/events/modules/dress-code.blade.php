{{-- resources/views/events/modules/dress-code.blade.php --}}

@php
    /** @var \App\Models\Event $event */
    /** @var \Illuminate\Support\Collection|\App\Models\EventDressCode[] $dressCodes */
@endphp

@if($dressCodes->isNotEmpty())
    <section class="bg-slate-800/40 border border-slate-700/60 rounded-2xl p-6 md:p-8 shadow-sm mb-8">
        <div class="mb-4">
            <h2 class="text-xl md:text-2xl font-semibold text-slate-50">
                Código de vestimenta
            </h2>
            <p class="text-sm text-slate-300 mt-1">
                Para que todos nos veamos increíbles, le recomendamos este código de vestimenta:
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
            @foreach($dressCodes as $dressCode)
                <article class="rounded-2xl border border-slate-700/60 bg-slate-900/40 p-5 md:p-6">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-50">
                                {{ $dressCode->title }}
                            </h3>

                            @if(!empty($dressCode->description))
                                <p class="text-sm text-slate-300 mt-1">
                                    {{ $dressCode->description }}
                                </p>
                            @endif
                        </div>

                        {{-- Icono opcional (solo visual, no es imagen) --}}
                        @if(!empty($dressCode->icon))
                            @php
                                $icon = strtolower((string) $dressCode->icon);

                                // Mapeo simple a emoji (en el futuro lo puede cambiar por SVGs)
                                $emoji = match ($icon) {
                                    'tie', 'suit' => '👔',
                                    'dress'       => '👗',
                                    'cocktail'    => '🍸',
                                    'casual'      => '🧢',
                                    default       => '✨',
                                };
                            @endphp
                            <span class="text-xl" title="{{ $dressCode->icon }}">{{ $emoji }}</span>
                        @endif
                    </div>

                    {{-- Imagen de ejemplos --}}
                    @if($dressCode->examplePhoto)
                        @php
                            $photo = $dressCode->examplePhoto;
                            $url = \Illuminate\Support\Facades\Storage::disk('public')->url(
                                $photo->thumbnail_path ?: $photo->file_path
                            );
                        @endphp

                        <div class="mt-4">
                            <p class="text-xs uppercase tracking-wide text-slate-400 mb-2">
                                Imagen de ejemplos
                            </p>

                            <div class="overflow-hidden rounded-xl border border-slate-700/60 bg-slate-950/30">
                                <img
                                    src="{{ $url }}"
                                    alt="Ejemplo de {{ $dressCode->title }}"
                                    loading="lazy"
                                    class="w-full h-44 object-cover"
                                >
                            </div>
                        </div>
                    @endif

                    {{-- Ejemplos en texto --}}
                    @if(!empty($dressCode->examples))
                        <div class="mt-4">
                            <p class="text-xs uppercase tracking-wide text-slate-400">
                                Ejemplos
                            </p>
                            <p class="text-sm text-slate-200 mt-1 whitespace-pre-line">
                                {{ $dressCode->examples }}
                            </p>
                        </div>
                    @endif

                    {{-- Notas --}}
                    @if(!empty($dressCode->notes))
                        <div class="mt-4">
                            <p class="text-xs uppercase tracking-wide text-slate-400">
                                Notas
                            </p>
                            <p class="text-sm text-slate-200 mt-1 whitespace-pre-line">
                                {{ $dressCode->notes }}
                            </p>
                        </div>
                    @endif
                </article>
            @endforeach
        </div>
    </section>
@endif
