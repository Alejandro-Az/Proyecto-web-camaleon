@extends('client.layout')

@section('title', 'Mis eventos')

@section('content')
    <section class="bg-[var(--surface-alt)] rounded-3xl p-6 md:p-8 shadow">
        <div class="flex items-center justify-between gap-4 mb-6">
            <div>
                <h2 class="text-xl font-semibold">Mis eventos</h2>
                <p class="text-sm text-[var(--ink-soft)]">Solo ve los eventos que le pertenecen a su cuenta.</p>
            </div>
            <a
                href="{{ route('client.events.create') }}"
                class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-medium bg-pink-500 text-[var(--ink)] hover:bg-pink-400 transition"
            >
                + Nuevo evento
            </a>
        </div>

        @if($events->isEmpty())
            <p class="text-[var(--ink-soft)]">No tiene eventos todavía.</p>
        @else
            <div class="grid gap-4">
                @foreach($events as $event)
                    <article class="rounded-2xl bg-[var(--surface-alt)] border border-[var(--rule)] p-5 flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <h3 class="text-lg font-semibold truncate">{{ $event->name }}</h3>

                            @if($event->type)
                                <p class="text-xs text-[var(--ink-muted)] uppercase tracking-[0.2em] mt-1">{{ $event->type }}</p>
                            @endif

                            @if($event->event_date)
                                <p class="text-sm text-[var(--ink-soft)] mt-2">
                                    Fecha: {{ $event->event_date->translatedFormat('d \de F \de Y') }}
                                </p>
                            @endif

                            @if($event->status)
                                <p class="text-xs text-[var(--ink-muted)] mt-1">Estatus: {{ $event->status }}</p>
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
                                    class="text-xs text-[var(--ink-soft)] hover:text-[var(--ink)] underline"
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
