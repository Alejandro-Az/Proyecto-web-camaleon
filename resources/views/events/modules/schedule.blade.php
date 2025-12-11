<section class="bg-slate-800/60 rounded-3xl p-6 md:p-8 shadow">
    <h2 class="text-xl font-semibold mb-4">
        Itinerario del evento
    </h2>

    @if($event->event_date)
        <p class="text-sm text-slate-300 mb-4">
            Todo el itinerario está planeado para el día
            <span class="font-medium">
                {{ $event->event_date->translatedFormat('d \\de F \\de Y') }}
            </span>.
        </p>
    @endif

    @if($schedules->isEmpty())
        <p class="text-sm text-slate-400">
            El itinerario aún no ha sido definido.
        </p>
    @else
        <ol class="space-y-4">
            @foreach($schedules as $item)
                <li class="flex gap-4">
                    {{-- Línea de tiempo visual --}}
                    <div class="flex flex-col items-center">
                        <span class="w-2 h-2 rounded-full bg-pink-500 mt-1"></span>
                        @if(!$loop->last)
                            <span class="flex-1 w-px bg-slate-700 mt-1"></span>
                        @endif
                    </div>

                    {{-- Contenido del bloque --}}
                    <div class="flex-1">
                        <p class="text-sm font-mono text-pink-200 mb-1">
                            {{ $item->starts_at->format('H:i') }}
                            @if($item->ends_at)
                                – {{ $item->ends_at->format('H:i') }} hrs
                            @endif
                        </p>

                        <p class="font-semibold">
                            {{ $item->title }}
                        </p>

                        @if($item->location_label)
                            <p class="text-sm text-slate-300">
                                {{ $item->location_label }}
                            </p>
                        @endif

                        @if($item->description)
                            <p class="text-sm text-slate-400 mt-1">
                                {{ $item->description }}
                            </p>
                        @endif
                    </div>
                </li>
            @endforeach
        </ol>
    @endif
</section>
