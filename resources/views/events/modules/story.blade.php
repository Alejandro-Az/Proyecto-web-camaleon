<div style="padding: 80px 24px; ">
    <div style="max-width: 1100px; margin: 0 auto;">
        <div style="text-align: center; margin-bottom: 40px;">
            <div class="cml-eyebrow" style="margin-bottom: 14px;">El comienzo</div>
            <h2 class="cml-serif text-3xl md:text-4xl italic text-[var(--ink)]">Nuestra Historia</h2>
            <div class="cml-divider max-w-[80px] mx-auto mt-6 mb-8"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 19c0-8 6-14 14-14 0 8-6 14-14 14z"/><path d="M5 19c4-4 8-6 12-8"/></svg></div>
        </div>

    

    @php
        $intro = data_get($event->settings, 'story_intro');
    @endphp

    @if($intro)
        <p class="text-sm text-[var(--ink-soft)] mb-6">{{ $intro }}</p>
    @else
        <p class="text-sm text-[var(--ink-soft)] mb-6">
            Un poquito sobre este momento especial.
        </p>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($stories as $story)
            <article class="cml-card" style="padding: 0; overflow: hidden;">
                @if($story->examplePhoto)
                    <div class="overflow-hidden" style="border-bottom: 1px solid var(--rule);">
                        <img
                            src="{{ asset('storage/' . $story->examplePhoto->file_path) }}"
                            alt="{{ $story->examplePhoto->caption ?: 'Imagen de historia' }}"
                            class="w-full object-cover"
                            style="height: 220px;"
                            loading="lazy"
                        >
                    </div>
                @endif

                <div style="padding: 28px 28px 32px;">
                    <h3 class="cml-serif" style="font-size: 22px; color: var(--ink); margin: 0 0 6px;">{{ $story->title }}</h3>

                    @if($story->subtitle)
                        <p class="cml-eyebrow" style="font-size: 10px; margin-bottom: 14px;">{{ $story->subtitle }}</p>
                    @endif

                    @if($story->body)
                        <div class="cml-sans" style="font-size: 14px; color: var(--ink-soft); line-height: 1.65;">
                            {!! nl2br(e($story->body)) !!}
                        </div>
                    @endif
                </div>
            </article>
        @endforeach
    </div>

    </div>
</div>
