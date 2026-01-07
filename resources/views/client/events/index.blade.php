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
            <div class="space-y-3">
                @foreach($events as $event)
                    <div class="rounded-2xl border border-slate-700 bg-slate-900/40 px-4 py-3">
                        <p class="text-lg font-semibold">{{ $event->name }}</p>
                        @if($event->event_date)
                            <p class="text-sm text-slate-300">
                                Fecha: {{ $event->event_date->translatedFormat('d \\de F \\de Y') }}
                            </p>
                        @endif
                        @if($event->status)
                            <p class="text-xs text-slate-400">Estatus: {{ $event->status }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </section>
@endsection
