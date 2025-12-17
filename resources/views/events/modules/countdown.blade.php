@php
    /** @var \App\Models\Event $event */

    $date = $event->event_date?->format('Y-m-d');
    $time = $event->start_time ?: '00:00:00';

    $targetIsoLocal = $date ? ($date.'T'.substr($time, 0, 8)) : null;

    $expiredLabel = data_get($event->settings, 'countdown_expired_label', '¡Ya comenzó!');
@endphp

@if($targetIsoLocal)
    <section class="bg-slate-800/60 rounded-3xl p-6 md:p-8 shadow"
             data-module="countdown"
             data-countdown-target="{{ $targetIsoLocal }}"
             data-countdown-expired-label="{{ $expiredLabel }}">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold">Cuenta regresiva</h2>
                <p class="text-sm text-slate-300">
                    Falta poquito…
                </p>
            </div>

            <div class="grid grid-cols-4 gap-3 text-center">
                <div class="rounded-2xl bg-slate-900/60 border border-slate-700 px-3 py-2">
                    <p class="text-2xl font-semibold" data-countdown-days>--</p>
                    <p class="text-xs text-slate-400">Días</p>
                </div>
                <div class="rounded-2xl bg-slate-900/60 border border-slate-700 px-3 py-2">
                    <p class="text-2xl font-semibold" data-countdown-hours>--</p>
                    <p class="text-xs text-slate-400">Horas</p>
                </div>
                <div class="rounded-2xl bg-slate-900/60 border border-slate-700 px-3 py-2">
                    <p class="text-2xl font-semibold" data-countdown-minutes>--</p>
                    <p class="text-xs text-slate-400">Min</p>
                </div>
                <div class="rounded-2xl bg-slate-900/60 border border-slate-700 px-3 py-2">
                    <p class="text-2xl font-semibold" data-countdown-seconds>--</p>
                    <p class="text-xs text-slate-400">Seg</p>
                </div>
            </div>
        </div>

        <p class="mt-4 text-sm text-slate-300" data-countdown-expired hidden>
            {{ $expiredLabel }}
        </p>
    </section>
@endif
