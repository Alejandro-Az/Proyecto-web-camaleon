<div id="rsvp" data-module="rsvp" style="padding: 90px 24px; max-width: 760px; margin: 0 auto;">
    <div style="text-align: center; margin-bottom: 44px;">
        <div class="cml-eyebrow" style="margin-bottom: 14px;">Confirmación</div>
        <h2 class="cml-serif text-4xl italic text-[var(--ink)]">¿Nos acompañas?</h2>
        <div class="cml-divider max-w-[80px] mx-auto mt-6 text-[var(--accent)]"><i data-lucide="leaf" class="w-4 h-4 mx-auto"></i></div>
        
        <div class="cml-sans text-center text-sm text-[var(--ink-muted)] mt-6 mx-auto max-w-lg">
            Te pedimos confirmar lo antes posible. Cada lugar es importante para nosotros.
        </div>
    </div>

    {{-- ✅ Alertas (AJAX sin recarga) --}}
    <div data-rsvp-alerts class="space-y-2"></div>

    {{-- Fallback mensajes (solo si hay recarga) --}}
    @if(session('rsvp_success'))
        <div class=" bg-emerald-500/10 border border-emerald-500/60  text-sm text-emerald-100">
            {{ session('rsvp_success') }}
        </div>
    @endif

    @if(session('rsvp_error'))
        <div class=" bg-red-500/10 border border-red-500/60  text-sm text-red-100">
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
            <div class="mt-4  border border-[var(--rule)] bg-[var(--surface)]/40 p-4">
                <p class="text-sm text-[var(--ink-soft)]">Tu lugar asignado:</p>
                <p class="text-lg font-semibold">{{ $guest->seat_label }}</p>
            </div>
        @endif

        {{-- ✅ RESUMEN (lo ocultamos cuando se edita sin recarga) --}}
        <div data-rsvp-summary class="{{ $showFormInitial ? 'hidden' : '' }}">
            @if($currentStatus !== \App\Models\Guest::RSVP_PENDING)
                <div class="   text-sm space-y-2">
                    <p class="text-[var(--ink)]">
                        Hola, <span class="font-semibold" data-rsvp-guest-name>{{ $guest->name }}</span>.
                    </p>

                    <p class="text-[var(--ink)]" data-rsvp-status-label>
                        {{ $statusLabels[$currentStatus] ?? 'Tu respuesta ha sido registrada.' }}
                    </p>

                    <div class="{{ $currentStatus === \App\Models\Guest::RSVP_YES ? '' : 'hidden' }}" data-rsvp-confirmed-row>
                        <p class="text-[var(--ink)]">
                            Personas confirmadas con esta invitación:
                            <span class="font-semibold" data-rsvp-guests-confirmed>
                                {{ $guest->guests_confirmed ?? 1 }}
                            </span>
                        </p>
                    </div>

                    <p class="text-[var(--ink-soft)] italic {{ $guest->rsvp_message ? '' : 'hidden' }}" data-rsvp-message-row>
                        “<span data-rsvp-message>{{ $guest->rsvp_message }}</span>”
                    </p>

                    <div class="pt-2 flex gap-2">
                        <button
                            type="button"
                            data-rsvp-edit
                            class="cml-btn cml-btn--ghost"
                            style="padding: 10px 20px; font-size: 11px;"
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
                <p class="text-sm text-[var(--ink)]">
                    Hola, <span class="font-semibold">{{ $guest->name }}</span>.
                    Este formulario es para la invitación con código
                    <span class="font-mono text-xs bg-[var(--surface)]/70  rounded">{{ $guest->invitation_code }}</span>.
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
                                       class="rounded-full border-[var(--rule)] text-[var(--accent)] focus:ring-pink-500">
                                <span>Sí asistiré</span>
                            </label>

                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="rsvp_status" value="no"
                                       @checked($selectedStatus === \App\Models\Guest::RSVP_NO)
                                       class="rounded-full border-[var(--rule)] text-[var(--accent)] focus:ring-pink-500">
                                <span>No podré asistir</span>
                            </label>

                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="rsvp_status" value="maybe"
                                       @checked($selectedStatus === \App\Models\Guest::RSVP_MAYBE)
                                       class="rounded-full border-[var(--rule)] text-[var(--accent)] focus:ring-pink-500">
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
                            <span class="text-xs text-[var(--ink-muted)]">(Sólo aplica si sí asistirá)</span>
                        </label>

                        @if($guest->invited_seats)
                            <p class="text-xs text-[var(--ink-muted)] mb-1">
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
                            class="w-24 cml-input bg-transparent border-b border-[var(--rule)] pb-2 text-[var(--ink)] focus:border-[var(--accent)] outline-none rounded-none text-center rounded-xl   text-sm text-[var(--ink)] focus:outline-none focus:ring-2 focus:ring-pink-500"
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
                            class="w-full cml-input bg-transparent border-b border-[var(--rule)] pb-2 text-[var(--ink)] focus:border-[var(--accent)] outline-none rounded-none    text-sm text-[var(--ink)] focus:outline-none focus:ring-2 focus:ring-pink-500"
                        >{{ old('rsvp_message', $guest->rsvp_message) }}</textarea>

                        @error('rsvp_message')
                            <p class="text-xs text-red-300 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Dieta / alergias --}}
                    <div class="mt-2 space-y-3">
                        <p class="text-sm text-[var(--ink-soft)]">Dieta / alergias (solo lo verá el anfitrión)</p>

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
                                        class="rounded border-[var(--rule)] text-[var(--accent)] focus:ring-pink-500"
                                    >
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>

                        <textarea
                            name="dietary_notes"
                            rows="3"
                            class="w-full cml-input bg-transparent border-b border-[var(--rule)] pb-2 text-[var(--ink)] focus:border-[var(--accent)] outline-none rounded-none    text-sm text-[var(--ink)] focus:outline-none focus:ring-2 focus:ring-pink-500"
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
                            class="mt-1 rounded border-[var(--rule)] text-[var(--accent)] focus:ring-pink-500"
                        >
                        <label for="show_in_public_list" class="text-sm text-[var(--ink)]">
                            Acepto que mi nombre aparezca en la lista pública de asistentes.
                        </label>
                    </div>

                    <div class="flex gap-2">
                        <button
                            type="submit"
                            class="cml-btn cml-btn--accent"
                        >
                            Guardar confirmación
                        </button>

                        <button
                            type="button"
                            data-rsvp-cancel
                            class="cml-btn cml-btn--ghost"
                        >
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @else
        <p class="text-sm text-[var(--ink-soft)]">
            Para confirmar su asistencia, utilice el enlace personalizado que recibió en su invitación.
            Si tiene problemas, contacte directamente a los organizadores.
        </p>
    @endif

    </div>
</div>
