<section class="bg-slate-800/60 rounded-3xl p-6 md:p-8 shadow">
    <h2 class="text-xl font-semibold mb-2">Historia</h2>

    @php
        $intro = data_get($event->settings, 'story_intro');
    @endphp

    @if($intro)
        <p class="text-sm text-slate-300 mb-6">{{ $intro }}</p>
    @else
        <p class="text-sm text-slate-300 mb-6">
            Un poquito sobre este momento especial.
        </p>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        @foreach($stories as $story)
            <article class="bg-slate-900/40 border border-slate-700 rounded-3xl p-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold">{{ $story->title }}</h3>

                        @if($story->subtitle)
                            <p class="text-sm text-slate-300 mt-1">{{ $story->subtitle }}</p>
                        @endif
                    </div>
                </div>

                @if($story->examplePhoto)
                    <div class="mt-4">
                        <p class="text-xs uppercase tracking-[0.25em] text-slate-400 mb-2">
                            Imagen
                        </p>

                        <div class="rounded-2xl overflow-hidden border border-slate-700 bg-slate-950/30">
                            <img
                                src="{{ asset('storage/' . $story->examplePhoto->file_path) }}"
                                alt="{{ $story->examplePhoto->caption ?: 'Imagen de historia' }}"
                                class="w-full h-56 object-cover"
                                loading="lazy"
                            >
                        </div>
                    </div>
                @endif

                @if($story->body)
                    <div class="mt-4 text-slate-200 leading-relaxed text-sm">
                        {!! nl2br(e($story->body)) !!}
                    </div>
                @endif
            </article>
        @endforeach
    </div>
</section>
