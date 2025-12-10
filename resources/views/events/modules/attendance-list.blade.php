@if($confirmedGuests->isEmpty())
    <section class="bg-slate-800/40 rounded-3xl p-6 md:p-8 border border-dashed border-slate-700">
        <h2 class="text-xl font-semibold mb-2">Lista de asistentes confirmados</h2>
        <p class="text-sm text-slate-300">
            Aún no hay asistentes que hayan confirmado su asistencia y aceptado aparecer en la lista pública.
        </p>
    </section>
@else
    @php
        $totalGuests = $confirmedGuests->reduce(function ($carry, $guest) {
            /** @var \App\Models\Guest $guest */
            $count = $guest->guests_confirmed ?? 1;

            if ($count < 1) {
                $count = 1;
            }

            return $carry + $count;
        }, 0);
    @endphp

    <section class="bg-slate-800/60 rounded-3xl p-6 md:p-8 shadow space-y-4">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-semibold">Lista de asistentes confirmados</h2>
                <p class="text-sm text-slate-300">
                    Mostramos sólo a quienes han confirmado su asistencia y aceptado aparecer en esta lista.
                </p>
            </div>
        </div>

        <p class="text-sm text-slate-200">
            Hasta ahora, <span class="font-semibold">{{ $confirmedGuests->count() }}</span>
            invitado(s) han confirmado su asistencia, con un total aproximado de
            <span class="font-semibold">{{ $totalGuests }}</span> persona(s).
        </p>

        <ul class="mt-4 divide-y divide-slate-700/60">
            @foreach($confirmedGuests as $guest)
                <li class="py-3 flex items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-slate-100">
                            {{ $guest->name }}
                        </p>
                        @if($guest->rsvp_message)
                            <p class="text-xs text-slate-300 italic">
                                “{{ $guest->rsvp_message }}”
                            </p>
                        @endif
                    </div>
                    <div class="text-right text-xs text-slate-300">
                        <p>
                            {{ $guest->guests_confirmed ?? 1 }}
                            {{ ($guest->guests_confirmed ?? 1) == 1 ? 'persona' : 'personas' }}
                        </p>
                    </div>
                </li>
            @endforeach
        </ul>
    </section>
@endif
