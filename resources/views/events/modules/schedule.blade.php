<div style="padding: 90px 80px; max-width: 1200px; margin: 0 auto;">
    <div style="text-align: center; margin-bottom: 60px;">
        <div class="cml-eyebrow" style="margin-bottom: 14px;">Itinerario</div>
        <h2 class="cml-serif text-4xl italic text-[var(--ink)]">Cronograma del día</h2>
        <div class="cml-divider max-w-[80px] mx-auto mt-6 text-[var(--accent)]"><i data-lucide="leaf" class="w-4 h-4 mx-auto"></i></div>
    </div>

    @if($schedules->isEmpty())
        <p class="text-sm text-[var(--ink-muted)] text-center">El itinerario aún no ha sido definido.</p>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-10 md:gap-20 items-start">
            <div class="cml-img-ph aspect-[4/5] w-full bg-[var(--surface-alt)] flex items-center justify-center text-[var(--ink-muted)] text-sm tracking-widest uppercase">
                FOTO · CEREMONIA
            </div>

            <ol class="space-y-8">
                @foreach($schedules as $item)
                    <li class="flex gap-6">
                        {{-- Timeline line --}}
                        <div class="flex flex-col items-center mt-1">
                            <span class="w-2.5 h-2.5 rounded-full bg-[var(--accent)]"></span>
                            @if(!$loop->last)
                                <span class="flex-1 w-px bg-[var(--rule)] mt-2 min-h-[40px]"></span>
                            @endif
                        </div>

                        {{-- Content --}}
                        <div class="flex-1 pb-4">
                            <p class="text-sm font-sans text-[var(--accent)] mb-1 uppercase tracking-wider font-semibold">
                                {{ $item->starts_at->format('H:i') }}
                                @if($item->ends_at)
                                    – {{ $item->ends_at->format('H:i') }} hrs
                                @endif
                            </p>
                            <p class="font-serif text-xl text-[var(--ink)] mb-1">
                                {{ $item->title }}
                            </p>
                            @if($item->location_label)
                                <p class="text-sm text-[var(--ink-soft)] font-sans">
                                    {{ $item->location_label }}
                                </p>
                            @endif
                            @if($item->description)
                                <p class="text-sm text-[var(--ink-muted)] mt-2 font-sans leading-relaxed">
                                    {{ $item->description }}
                                </p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>
        </div>
    @endif
</div>