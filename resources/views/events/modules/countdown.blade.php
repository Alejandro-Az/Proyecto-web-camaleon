<div style="padding: 80px 24px; background: var(--surface-alt);">
    <div style="max-width: 900px; margin: 0 auto; text-align: center;">

        <div class="cml-eyebrow" style="margin-bottom: 14px;">El gran día</div>
        <h2 class="cml-serif" style="font-size: clamp(28px, 4vw, 44px); margin: 0; color: var(--ink); font-weight: 500;">Cuenta regresiva</h2>
        <div class="cml-divider" style="max-width: 80px; margin: 16px auto 48px;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 19c0-8 6-14 14-14 0 8-6 14-14 14z"/><path d="M5 19c4-4 8-6 12-8"/></svg>
        </div>

        @php
            /** @var \App\Models\Event $event */
            $date = $event->event_date?->format('Y-m-d');
            $time = $event->start_time ?: '00:00:00';
            $targetIsoLocal = $date ? ($date.'T'.substr($time, 0, 8)) : null;
            $expiredLabel = data_get($event->settings, 'countdown_expired_label', '¡Ya comenzó!');
        @endphp

        @if($targetIsoLocal)
            <div style="display: flex; gap: 40px; justify-content: center; align-items: flex-start;">
                <div>
                    <div class="cml-serif" style="font-size: 52px; color: var(--ink); font-variant-numeric: tabular-nums; line-height: 1;" data-countdown-days>--</div>
                    <div class="cml-eyebrow" style="font-size: 9.5px; margin-top: 10px; color: var(--ink-muted);">Días</div>
                </div>
                <div>
                    <div class="cml-serif" style="font-size: 52px; color: var(--ink); font-variant-numeric: tabular-nums; line-height: 1;" data-countdown-hours>--</div>
                    <div class="cml-eyebrow" style="font-size: 9.5px; margin-top: 10px; color: var(--ink-muted);">Hrs</div>
                </div>
                <div>
                    <div class="cml-serif" style="font-size: 52px; color: var(--ink); font-variant-numeric: tabular-nums; line-height: 1;" data-countdown-minutes>--</div>
                    <div class="cml-eyebrow" style="font-size: 9.5px; margin-top: 10px; color: var(--ink-muted);">Min</div>
                </div>
                <div>
                    <div class="cml-serif" style="font-size: 52px; color: var(--ink); font-variant-numeric: tabular-nums; line-height: 1;" data-countdown-seconds>--</div>
                    <div class="cml-eyebrow" style="font-size: 9.5px; margin-top: 10px; color: var(--ink-muted);">Seg</div>
                </div>
            </div>

            <p class="cml-sans" style="font-size: 13px; color: var(--ink-soft); margin-top: 20px; display: none;" data-countdown-expired>
                {{ $expiredLabel }}
            </p>
        @endif

    </div>
</div>
