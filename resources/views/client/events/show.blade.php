@extends('client.layout')

@section('title', 'Administrar evento')

@section('content')
    <section class="bg-slate-800/60 rounded-3xl p-6 md:p-8 shadow space-y-6">
        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
            <div class="min-w-0">
                <h2 class="text-xl font-semibold truncate">{{ $event->name }}</h2>

                @if($event->type)
                    <p class="text-xs text-slate-400 uppercase tracking-[0.2em] mt-1">{{ $event->type }}</p>
                @endif

                <div class="mt-3 space-y-1">
                    @if($event->event_date)
                        <p class="text-sm text-slate-300">
                            Fecha:
                            <span class="font-medium text-slate-100">
                                {{ $event->event_date->translatedFormat('d \de F \de Y') }}
                            </span>
                        </p>
                    @endif

                    @if($event->start_time)
                        <p class="text-sm text-slate-300">
                            Horario:
                            <span class="font-medium text-slate-100">
                                {{ \Illuminate\Support\Carbon::parse($event->start_time)->format('H:i') }}
                                @if($event->end_time)
                                    – {{ \Illuminate\Support\Carbon::parse($event->end_time)->format('H:i') }} hrs
                                @endif
                            </span>
                        </p>
                    @endif

                    @if($event->status)
                        <p class="text-xs text-slate-400">Estatus: {{ $event->status }}</p>
                    @endif

                    @if($event->slug)
                        <p class="text-xs text-slate-400">
                            URL pública:
                            <a class="underline hover:text-white" target="_blank" rel="noopener noreferrer"
                               href="{{ url('/eventos/' . $event->slug) }}">
                                {{ url('/eventos/' . $event->slug) }}
                            </a>
                        </p>
                    @endif
                </div>
            </div>

            <div class="shrink-0 flex flex-col items-start md:items-end gap-2">
                <a
                    href="{{ route('client.events.index') }}"
                    class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-medium bg-slate-900/70 border border-slate-700 text-slate-100 hover:bg-slate-900 transition"
                >
                    Volver a mis eventos
                </a>

                @if($event->slug)
                    <a
                        href="{{ url('/eventos/' . $event->slug) }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-medium bg-slate-100 text-slate-900 hover:bg-white transition"
                    >
                        Ver página pública
                    </a>
                @endif
            </div>
        </div>

        <div class="rounded-2xl bg-slate-900/40 border border-slate-700 p-5">
            <h3 class="text-base font-semibold mb-2">Siguiente paso</h3>
            <p class="text-sm text-slate-300">
                Aquí vamos a agregar: configuración general, toggles de módulos, settings base y hero/banner (en los siguientes incrementos).
            </p>
        </div>
    </section>
@endsection
