{{-- resources/views/events/modules/gallery.blade.php --}}

@php
    /** @var \App\Models\Event $event */
    /** @var \Illuminate\Support\Collection|\App\Models\EventPhoto[] $galleryPhotos */
@endphp

@if($galleryPhotos->isNotEmpty())
    <section class="bg-slate-800/40 border border-slate-700/60 rounded-2xl p-6 md:p-8 shadow-sm mb-8">
        <div class="flex items-baseline justify-between gap-4 mb-4">
            <div>
                <h2 class="text-xl md:text-2xl font-semibold text-slate-50">
                    Galería de fotos
                </h2>
                <p class="text-sm text-slate-300 mt-1">
                    Algunos momentos especiales del evento.
                </p>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-4">
            @foreach($galleryPhotos as $photo)
                <figure class="relative overflow-hidden rounded-xl bg-slate-900/60 border border-slate-700/60">
                    @php
                        $url = \Illuminate\Support\Facades\Storage::disk('public')->url(
                            $photo->thumbnail_path ?: $photo->file_path
                        );
                    @endphp

                    <img
                        src="{{ $url }}"
                        alt="{{ $photo->caption ?: 'Foto del evento' }}"
                        loading="lazy"
                        class="w-full h-32 md:h-40 lg:h-48 object-cover transition-transform duration-200 hover:scale-105"
                    >

                    @if($photo->caption)
                        <figcaption class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-slate-950/80 via-slate-950/40 to-transparent px-3 py-2">
                            <p class="text-xs md:text-sm text-slate-100 line-clamp-2">
                                {{ $photo->caption }}
                            </p>
                        </figcaption>
                    @endif
                </figure>
            @endforeach
        </div>
    </section>
@endif
