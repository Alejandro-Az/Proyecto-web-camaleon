@extends('client.layout')

@section('title', 'Mis eventos')

@section('content')
    <section class="bg-slate-800/60 rounded-3xl p-6 md:p-8 shadow">
        <div class="flex items-center justify-between gap-4 mb-6">
            <div>
                <h2 class="text-xl font-semibold">Mis eventos</h2>
                <p class="text-sm text-slate-300">Solo ve los eventos que le pertenecen a su cuenta.</p>
            </div>
        </div>

        @if($events->isEmpty())
            <p class="text-slate-300">No tiene eventos todavía.</p>
        @else
            <div class="grid gap-4">
                @foreach($events as $event)
                    <article class="rounded-2xl bg-slate-900/50 border border-slate-700 p-5 flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <h3 class="text-lg font-semibold truncate">{{ $event->name }}</h3>

                            @if($event->type)
                                <p class="text-xs text-slate-400 uppercase tracking-[0.2em] mt-1">{{ $event->type }}</p>
                            @endif

                            @if($event->event_date)
                                <p class="text-sm text-slate-300 mt-2">
                                    Fecha: {{ $event->event_date->translatedFormat('d \de F \de Y') }}
                                </p>
                            @endif

                            @if($event->status)
                                <p class="text-xs text-slate-400 mt-1">Estatus: {{ $event->status }}</p>
                            @endif
                        </div>

                        <div class="shrink-0 flex flex-col items-end gap-2">
                            <a
                                href="{{ route('client.events.show', $event->id) }}"
                                class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-medium bg-slate-100 text-slate-900 hover:bg-white transition"
                            >
                                Administrar
                            </a>

                            @if($event->slug)
                                <a
                                    href="{{ url('/eventos/' . $event->slug) }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="text-xs text-slate-300 hover:text-white underline"
                                >
                                    Ver página pública
                                </a>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
@endsection
