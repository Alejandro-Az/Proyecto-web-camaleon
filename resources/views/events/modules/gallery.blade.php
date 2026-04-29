<div style="padding: 80px 24px; background: var(--surface-alt);">
    <div style="max-width: 1100px; margin: 0 auto;">
        <div style="text-align: center; margin-bottom: 50px;"><div class="cml-eyebrow" style="margin-bottom: 14px;">Galería compartida</div><h2 class="cml-serif text-4xl italic text-[var(--ink)]">Memorias de los invitados</h2><div class="cml-divider max-w-[80px] mx-auto mt-6 text-[var(--accent)]"><i data-lucide="leaf" class="w-4 h-4 mx-auto"></i></div></div>
{{-- resources/views/events/modules/gallery.blade.php --}}

@php
    /** @var \App\Models\Event $event */
    /** @var \Illuminate\Support\Collection|\App\Models\EventPhoto[] $galleryPhotos */
@endphp

@if($galleryPhotos->isNotEmpty())
    
        <div class="flex items-baseline justify-between gap-4 mb-4">
            <div>
                
                <p class="text-sm text-[var(--ink-soft)] mt-1">
                    Algunos momentos especiales del evento.
                </p>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 gap-2 md:gap-3">
            @foreach($galleryPhotos as $photo)
                <figure class="relative overflow-hidden cml-card" style="border-radius: 0;">
                    @php
                        $url = \Illuminate\Support\Facades\Storage::disk('public')->url(
                            $photo->thumbnail_path ?: $photo->file_path
                        );
                    @endphp

                    <img
                        src="{{ $url }}"
                        alt="{{ $photo->caption ?: 'Foto del evento' }}"
                        loading="lazy"
                        class="w-full h-40 md:h-52 object-cover"
                    >

                    @if($photo->caption)
                        <figcaption class="absolute inset-x-0 bottom-0 px-3 py-2" style="background: linear-gradient(to top, rgba(0,0,0,0.65) 0%, transparent 100%);">
                            <p class="cml-sans text-xs text-white line-clamp-2">{{ $photo->caption }}</p>
                        </figcaption>
                    @endif
                </figure>
            @endforeach
        </div>
    </div>
@endif

    </div>
</div>
