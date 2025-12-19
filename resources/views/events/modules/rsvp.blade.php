<section id="rsvp"
         data-module="rsvp"
         class="bg-slate-800/60 rounded-3xl p-6 md:p-8 shadow space-y-4">

    <div class="flex items-center justify-between gap-3">
        <div>
            <h2 class="text-xl font-semibold">Confirmar asistencia (RSVP)</h2>
            <p class="text-sm text-slate-300">
                Ayúdenos a organizar mejor el evento confirmando su asistencia.
            </p>
        </div>
    </div>

    {{-- ✅ Alertas (AJAX sin recarga) --}}
    <div data-rsvp-alerts class="space-y-2"></div>

    {{-- Fallback mensajes (solo si hay recarga) --}}
    @if(session('rsvp_success'))
        <div class="rounded-2xl bg-emerald-500/10 border border-emerald-500/60 px-4 py-3 text-sm text-emerald-100">
            {{ session('rsvp_success') }}
        </div>
    @endif

    @if(session('rsvp_error'))
        <div class="rounded-2xl bg-red-500/10 border border-red-500/60 px-4 py-3 text-sm text-red-100">
            {{ session('rsvp_error') }}
        </div>
    @endif

    @if($guest)
        @php
            /** @var \App\Models\Guest $guest */
            $currentStatus = $guest->rsvp_status;
            $editMode = (bool) ($rsvpEditMode ?? false);

            // Si hay errores (fallback sin JS), forzamos mostrar el form
            if ($errors->any()) {
                $editMode = true;
            }

            $statusLabels = [
                \App\Models\Guest::RSVP_YES   => 'Has confirmado tu asistencia.',
                \App\Models\Guest::RSVP_NO    => 'Has indicado que no podrás asistir.',
                \App\Models\Guest::RSVP_MAYBE => 'Has indicado que aún no estás seguro(a).',
            ];

            // Mostrar form al inicio si está pending o viene edit=1 o hay errores
            $showFormInitial = $editMode || $currentStatus === \App\Models\Guest::RSVP_PENDING;
        @endphp

        {{-- Asiento asignado --}}
        @if($guest->seat_label)
            <div class="mt-4 rounded-2xl border border-slate-700 bg-slate-900/40 p-4">
                <p class="text-sm text-slate-300">Tu lugar asignado:</p>
                <p class="text-lg font-semibold">{{ $guest->seat_label }}</p>
            </div>
        @endif

        {{-- ✅ RESUMEN (lo ocultamos cuando se edita sin recarga) --}}
        <div data-rsvp-summary class="{{ $showFormInitial ? 'hidden' : '' }}">
            @if($currentStatus !== \App\Models\Guest::RSVP_PENDING)
                <div class="rounded-2xl bg-slate-900/70 border border-slate-700 px-4 py-3 text-sm space-y-2">
                    <p class="text-slate-200">
                        Hola, <span class="font-semibold" data-rsvp-guest-name>{{ $guest->name }}</span>.
                    </p>

                    <p class="text-slate-100" data-rsvp-status-label>
                        {{ $statusLabels[$currentStatus] ?? 'Tu respuesta ha sido registrada.' }}
                    </p>

                    <div class="{{ $currentStatus === \App\Models\Guest::RSVP_YES ? '' : 'hidden' }}" data-rsvp-confirmed-row>
                        <p class="text-slate-200">
                            Personas confirmadas con esta invitación:
                            <span class="font-semibold" data-rsvp-guests-confirmed>
                                {{ $guest->guests_confirmed ?? 1 }}
                            </span>
                        </p>
                    </div>

                    <p class="text-slate-300 italic {{ $guest->rsvp_message ? '' : 'hidden' }}" data-rsvp-message-row>
                        “<span data-rsvp-message>{{ $guest->rsvp_message }}</span>”
                    </p>

                    <div class="pt-2 flex gap-2">
                        <button
                            type="button"
                            data-rsvp-edit
                            class="inline-flex items-center justify-center px-4 py-2 rounded-full border border-pink-500 text-pink-300 hover:bg-pink-500/10 text-xs font-semibold"
                        >
                            Editar mi respuesta
                        </button>
                    </div>
                </div>
            @endif
        </div>

        {{-- ✅ FORM (siempre existe; se muestra/oculta sin recarga) --}}
        <div data-rsvp-form-wrapper class="{{ $showFormInitial ? '' : 'hidden' }}">
            <div class="space-y-4 mt-4">
                <p class="text-sm text-slate-200">
                    Hola, <span class="font-semibold">{{ $guest->name }}</span>.
                    Este formulario es para la invitación con código
                    <span class="font-mono text-xs bg-slate-900/70 px-2 py-1 rounded">{{ $guest->invitation_code }}</span>.
                </p>

                <form data-rsvp-form method="POST"
                      action="{{ route('events.rsvp.store', ['slug' => $event->slug]) }}"
                      class="space-y-4">
                    @csrf

                    <input type="hidden" name="invitation_code" value="{{ $guest->invitation_code }}">

                    {{-- Estado --}}
                    <div>
                        <p class="text-sm font-medium mb-2">¿Asistirá al evento?</p>
                        <div class="flex flex-wrap gap-3 text-sm">
                            @php $selectedStatus = old('rsvp_status', $currentStatus); @endphp

                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="rsvp_status" value="yes"
                                       @checked($selectedStatus === \App\Models\Guest::RSVP_YES)
                                       class="rounded-full border-slate-500 text-pink-500 focus:ring-pink-500">
                                <span>Sí asistiré</span>
                            </label>

                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="rsvp_status" value="no"
                                       @checked($selectedStatus === \App\Models\Guest::RSVP_NO)
                                       class="rounded-full border-slate-500 text-pink-500 focus:ring-pink-500">
                                <span>No podré asistir</span>
                            </label>

                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="rsvp_status" value="maybe"
                                       @checked($selectedStatus === \App\Models\Guest::RSVP_MAYBE)
                                       class="rounded-full border-slate-500 text-pink-500 focus:ring-pink-500">
                                <span>Aún no estoy seguro(a)</span>
                            </label>
                        </div>

                        @error('rsvp_status')
                            <p class="text-xs text-red-300 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Confirmados --}}
                    <div>
                        <label class="block text-sm font-medium mb-1" for="guests_confirmed">
                            ¿Cuántas personas asistirán con esta invitación?
                            <span class="text-xs text-slate-400">(Sólo aplica si sí asistirá)</span>
                        </label>

                        @if($guest->invited_seats)
                            <p class="text-xs text-slate-400 mb-1">
                                Con esta invitación puede confirmar hasta
                                <span class="font-semibold">{{ $guest->invited_seats }}</span>
                                {{ $guest->invited_seats == 1 ? 'persona' : 'personas' }}.
                            </p>
                        @endif

                        <input
                            id="guests_confirmed"
                            type="number"
                            name="guests_confirmed"
                            min="1"
                            max="20"
                            value="{{ old('guests_confirmed', max(1, (int) ($guest->guests_confirmed ?? 1))) }}"
                            class="w-24 rounded-xl bg-slate-900/70 border border-slate-600 px-3 py-2 text-sm text-slate-100 focus:outline-none focus:ring-2 focus:ring-pink-500"
                        >

                        @error('guests_confirmed')
                            <p class="text-xs text-red-300 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Mensaje --}}
                    <div>
                        <label class="block text-sm font-medium mb-1" for="rsvp_message">
                            Mensaje para los anfitriones (opcional)
                        </label>

                        <textarea
                            id="rsvp_message"
                            name="rsvp_message"
                            rows="3"
                            class="w-full rounded-2xl bg-slate-900/70 border border-slate-600 px-3 py-2 text-sm text-slate-100 focus:outline-none focus:ring-2 focus:ring-pink-500"
                        >{{ old('rsvp_message', $guest->rsvp_message) }}</textarea>

                        @error('rsvp_message')
                            <p class="text-xs text-red-300 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Dieta / alergias --}}
                    <div class="mt-2 space-y-3">
                        <p class="text-sm text-slate-300">Dieta / alergias (solo lo verá el anfitrión)</p>

                        @php
                            $tags = [
                                'vegano' => 'Vegano',
                                'vegetariano' => 'Vegetariano',
                                'sin_gluten' => 'Sin gluten',
                                'diabetico' => 'Diabético',
                                'sin_lactosa' => 'Sin lactosa',
                                'alergia_nueces' => 'Alergia a nueces',
                            ];

                            $currentDiet = old(
                                'dietary_tags',
                                is_array($guest->dietary_tags ?? null) ? $guest->dietary_tags : []
                            );
                            $currentDiet = is_array($currentDiet) ? $currentDiet : [];
                        @endphp

                        <div class="grid grid-cols-2 gap-2 text-sm">
                            @foreach($tags as $k => $label)
                                <label class="flex items-center gap-2">
                                    <input
                                        type="checkbox"
                                        name="dietary_tags[]"
                                        value="{{ $k }}"
                                        @checked(in_array($k, $currentDiet, true))
                                        class="rounded border-slate-500 text-pink-500 focus:ring-pink-500"
                                    >
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>

                        <textarea
                            name="dietary_notes"
                            rows="3"
                            class="w-full rounded-2xl bg-slate-900/70 border border-slate-600 px-3 py-2 text-sm text-slate-100 focus:outline-none focus:ring-2 focus:ring-pink-500"
                            placeholder="Notas extra (opcional): alergias específicas, etc."
                        >{{ old('dietary_notes', $guest->dietary_notes) }}</textarea>

                        @error('dietary_notes')
                            <p class="text-xs text-red-300 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Lista pública --}}
                    <div class="flex items-start gap-2">
                        <input
                            id="show_in_public_list"
                            type="checkbox"
                            name="show_in_public_list"
                            value="1"
                            @checked(old('show_in_public_list', $guest->show_in_public_list))
                            class="mt-1 rounded border-slate-500 text-pink-500 focus:ring-pink-500"
                        >
                        <label for="show_in_public_list" class="text-sm text-slate-200">
                            Acepto que mi nombre aparezca en la lista pública de asistentes.
                        </label>
                    </div>

                    <div class="flex gap-2">
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center px-4 py-2 rounded-full bg-pink-500 hover:bg-pink-400 text-sm font-semibold shadow"
                        >
                            Guardar confirmación
                        </button>

                        <button
                            type="button"
                            data-rsvp-cancel
                            class="inline-flex items-center justify-center px-4 py-2 rounded-full border border-slate-600 text-slate-200 hover:bg-slate-700/40 text-sm font-semibold"
                        >
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @else
        <p class="text-sm text-slate-300">
            Para confirmar su asistencia, utilice el enlace personalizado que recibió en su invitación.
            Si tiene problemas, contacte directamente a los organizadores.
        </p>
    @endif
</section>
