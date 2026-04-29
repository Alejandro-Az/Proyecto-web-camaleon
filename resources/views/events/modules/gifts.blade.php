<div style="padding: 80px 24px; background: var(--surface-alt);">
    <div style="max-width: 1100px; margin: 0 auto;">
        <div style="text-align: center; margin-bottom: 50px;"><div class="cml-eyebrow" style="margin-bottom: 14px;">Comenzando un hogar</div><h2 class="cml-serif text-4xl italic text-[var(--ink)]">Mesa de regalos</h2><div class="cml-divider max-w-[80px] mx-auto mt-6 text-[var(--accent)]"><i data-lucide="leaf" class="w-4 h-4 mx-auto"></i></div><div class="cml-sans text-center text-sm text-[var(--ink-muted)] mt-6 mx-auto max-w-lg">Tu presencia es nuestro mayor regalo. Si gustas, puedes obsequiarnos algo de nuestra mesa.</div></div>
@php
    use App\Models\EventGift;

    /** @var \App\Models\Event $event */
    /** @var \Illuminate\Support\Collection|\App\Models\EventGift[] $gifts */
    /** @var \App\Models\Guest|null $guest */
    /** @var \Illuminate\Support\Collection $guestGiftClaimsByGiftId */

    $settings = $event->settings ?? [];

    $requireInvitationCode = (bool) data_get($settings, 'gifts_require_invitation_code', true);
    $allowUnclaim          = (bool) data_get($settings, 'gifts_allow_unclaim', false);
    $hidePurchased         = (bool) data_get($settings, 'gifts_hide_purchased_from_public', false);

    // NUEVOS (modulares)
    $showClaimersPublic    = (bool) data_get($settings, 'gifts_show_claimers_public', false);
    $allowMultiUnitReserve = (bool) data_get($settings, 'gifts_allow_multi_unit_reserve', false);
    $maxUnitsPerGuest      = max(1, (int) data_get($settings, 'gifts_max_units_per_guest_per_gift', 1));

    $guestGiftClaimsByGiftId = $guestGiftClaimsByGiftId ?? collect();
@endphp

<section id="event-gifts" data-module="gifts" data-event-id="{{ $event->id }}" data-event-slug="{{ $event->slug }}" data-require-invitation-code="{{ $requireInvitationCode ? '1' : '0' }}" data-allow-unclaim="{{ $allowUnclaim ? '1' : '0' }}" data-allow-multi-unit-reserve="{{ $allowMultiUnitReserve ? '1' : '0' }}" data-show-claimers="{{ $showClaimersPublic ? '1' : '0' }}" data-max-units-per-guest="{{ $maxUnitsPerGuest }}" data-invitation-code="{{ $guest->invitation_code ?? '' }}" data-gifts-summary-url="{{ route('events.gifts.summary', ['slug' => $event->slug]) }}" data-my-claims-url="{{ route('events.gifts.myClaims', ['slug' => $event->slug]) }}" class="space-y-4">
    <header>
        
        <p class="text-sm text-[var(--ink-soft)]">
            Aquí puede ver las sugerencias de regalos para {{ $event->owner_name ?? $event->name }}.
        </p>
    </header>

    {{-- Mensajes AJAX (sin recargar) --}}
    <div data-gifts-success class="hidden rounded-2xl px-4 py-3 text-sm border border-emerald-500/60 bg-emerald-500/10 text-emerald-100"></div>
    <div data-gifts-errors class="hidden rounded-2xl px-4 py-3 text-sm border border-red-500/60 bg-red-500/10 text-red-100">
        <ul class="list-disc pl-5 space-y-1"></ul>
    </div>

    {{-- Captura opcional de código (por si entró sin ?i=) --}}
    @if($requireInvitationCode && ! $guest)
        <div class="rounded-2xl p-4 border border-[var(--rule)] bg-[var(--surface)]/40 space-y-2" data-gifts-code-box>
            <p class="text-sm text-[var(--ink)]">
                Para apartar regalos necesita su <span class="font-semibold">código de invitación</span>.
                Idealmente entre con su enlace personal (trae <code>?i=...</code>).
            </p>

            <div class="flex flex-col sm:flex-row gap-2">
                <input
                    type="text"
                    class="w-full sm:max-w-sm rounded-xl bg-[var(--surface)] border border-[var(--rule)] px-3 py-2 text-sm text-[var(--ink)]"
                    placeholder="Pegue su código de invitación"
                    data-gifts-code-input
                >
                <button
                    type="button"
                    class="inline-flex items-center justify-center px-4 py-2 rounded-xl text-sm font-semibold cml-btn cml-btn--accent transition"
                    data-gifts-code-save
                >
                    Guardar código
                </button>
                <button
                    type="button"
                    class="inline-flex items-center justify-center px-4 py-2 rounded-xl text-sm font-semibold bg-[var(--surface-alt)] hover:bg-[var(--surface-alt)] text-[var(--ink)] transition"
                    data-gifts-code-clear
                >
                    Quitar
                </button>
            </div>

            <p class="text-xs text-[var(--ink-muted)]">
                Nota: el código se guarda en su navegador para que no se “olvide” al recargar.
            </p>
        </div>
    @endif

    @if($gifts->isEmpty())
        <p class="text-sm text-[var(--ink-muted)]">Aún no hay regalos configurados para este evento.</p>
    @else
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-12">
            @foreach($gifts as $gift)
                @php
                    /** @var EventGift $gift */
                    $totalQty     = max(1, (int) ($gift->quantity ?? 1));
                    $reservedQty  = max(0, (int) ($gift->quantity_reserved ?? 0));
                    $availableQty = max(0, $totalQty - $reservedQty);

                    $isPurchased  = $gift->status === EventGift::STATUS_PURCHASED;
                    $isSoldOut    = $availableQty <= 0;

                    $statusLabel = 'Disponible';
                    $statusClass = 'text-[var(--accent)]';

                    if ($isPurchased) {
                        $statusLabel = 'Comprado';
                        $statusClass = 'text-[var(--ink-muted)]';
                    } elseif ($isSoldOut) {
                        $statusLabel = 'Sin unidades disponibles';
                        $statusClass = 'text-amber-400';
                    }

                    $guestClaim    = $guest ? $guestGiftClaimsByGiftId->get($gift->id) : null;
                    $guestClaimQty = $guestClaim ? (int) $guestClaim->quantity : 0;

                    $guestHasReserved = $guestClaimQty > 0;
                    $guestReachedMax  = $guestClaimQty >= $maxUnitsPerGuest;

                    $canClaim = ! $isPurchased
                        && $availableQty > 0
                        && ! $guestReachedMax
                        && (! $requireInvitationCode || $guest);

                    $canUnclaim = $allowUnclaim && $guestHasReserved;

                    // Para UX inmediata del input quantity (sin esperar a refreshSummary)
                    $maxQtyInput = max(1, min($availableQty, max(0, $maxUnitsPerGuest - $guestClaimQty)));
                @endphp

                @if($hidePurchased && $isPurchased)
                    @continue
                @endif

                <article
                    class="cml-card flex flex-col justify-between"
                    style="padding: 0; overflow: hidden;"
                    data-gift-card
                    data-gift-id="{{ $gift->id }}"
                    data-gift-status="{{ $gift->status }}"
                    data-gift-total="{{ $totalQty }}"
                    data-gift-reserved="{{ $reservedQty }}"
                    data-gift-available="{{ $availableQty }}"
                    data-gift-my-qty="{{ $guestClaimQty }}"
                >
                    <div style="padding: 16px 18px 14px; flex: 1;">
                        <div class="flex items-start justify-between gap-3" style="margin-bottom: 8px;">
                            <div>
                                <h3 class="cml-serif" style="font-size: 17px; color: var(--ink); margin: 0;">{{ $gift->name }}</h3>

                                @if($gift->store_label)
                                    <p class="cml-eyebrow" style="font-size: 10px; color: var(--ink-muted); margin-top: 4px;">{{ $gift->store_label }}</p>
                                @endif
                            </div>

                            <span class="cml-sans text-xs font-medium {{ $statusClass }}" data-gift-status-label>
                                {{ $guestHasReserved ? 'Apartado por ti' : $statusLabel }}
                            </span>
                        </div>

                        @if($guestHasReserved)
                            <p class="text-xs text-[var(--ink-soft)]" data-gift-my-qty-text>
                                Usted apartó {{ $guestClaimQty }} / {{ $maxUnitsPerGuest }} unidad(es).
                            </p>
                        @else
                            <p class="text-xs text-[var(--ink-soft)] hidden" data-gift-my-qty-text></p>
                        @endif

                        @if($gift->description)
                            <p class="text-sm text-[var(--ink)]">{{ $gift->description }}</p>
                        @endif

                        @if($gift->url)
                            <a
                                href="{{ $gift->url }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center text-xs font-semibold underline text-sky-300 hover:text-sky-200"
                            >
                                Ver en tienda
                            </a>
                        @endif

                        <p class="text-xs text-[var(--ink-soft)]" data-gift-availability-text>
                            @if($availableQty <= 0)
                                No quedan unidades disponibles ({{ $totalQty }} en total).
                            @elseif($availableQty === 1)
                                Queda {{ $availableQty }} unidad disponible (de {{ $totalQty }}).
                            @else
                                Quedan {{ $availableQty }} unidades disponibles (de {{ $totalQty }}).
                            @endif
                        </p>

                        @if($showClaimersPublic)
                            <div class="pt-2 border-t border-[var(--rule)]/60">
                                <p class="text-[11px] text-[var(--ink-muted)] mb-1">Invitados comprometidos:</p>
                                <ul class="space-y-1 text-xs text-[var(--ink)]" data-gift-claimers-list>
                                    <li class="text-[var(--ink-muted)]">Cargando…</li>
                                </ul>
                            </div>
                        @endif
                    </div>

                    <div style="padding: 12px 18px 18px; border-top: 1px solid var(--rule);">
                        <div class="flex flex-wrap items-center gap-2">
                            {{-- Reservar --}}
                            <form
                                method="POST"
                                action="{{ route('events.gifts.reserve', ['slug' => $event->slug, 'gift' => $gift->id]) }}"
                                class="{{ $canClaim ? '' : 'hidden' }}"
                                data-gift-form="reserve"
                            >
                                @csrf
                                <input type="hidden" name="invitation_code" value="{{ $guest->invitation_code ?? '' }}" data-gift-invitation-code>

                                @if($allowMultiUnitReserve)
                                    <input
                                        type="number"
                                        name="quantity"
                                        min="1"
                                        max="{{ $maxQtyInput }}"
                                        step="1"
                                        value="1"
                                        class="cml-input"
                                        style="width: 70px; padding: 6px 10px; font-size: 12px;"
                                        data-gift-quantity-input
                                    >
                                @endif

                                <button
                                    type="submit"
                                    class="cml-btn cml-btn--accent"
                                    style="padding: 10px 16px; font-size: 11px;"
                                    data-gift-button="reserve"
                                >
                                    Lo compro yo
                                </button>
                            </form>

                            {{-- Liberar --}}
                            <form
                                method="POST"
                                action="{{ route('events.gifts.unreserve', ['slug' => $event->slug, 'gift' => $gift->id]) }}"
                                class="{{ $canUnclaim ? '' : 'hidden' }}"
                                data-gift-form="unreserve"
                            >
                                @csrf
                                <input type="hidden" name="invitation_code" value="{{ $guest->invitation_code ?? '' }}" data-gift-invitation-code>

                                <button
                                    type="submit"
                                    class="cml-btn cml-btn--ghost"
                                    style="padding: 10px 16px; font-size: 11px;"
                                    data-gift-button="unreserve"
                                >
                                    Me arrepentí
                                </button>
                            </form>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif

    </div>
</div>
