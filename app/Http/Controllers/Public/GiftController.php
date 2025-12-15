<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventGift;
use App\Models\EventGiftClaim;
use App\Models\Guest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;

class GiftController extends Controller
{
    public function summary(string $slug, Request $request)
    {
        $event = Event::publicVisible()->where('slug', $slug)->firstOrFail();

        if (!data_get($event->modules, 'gifts')) {
            abort(404);
        }

        $settings = $event->settings ?? [];
        $requireCode  = (bool) data_get($settings, 'gifts_require_invitation_code', true);
        $showClaimers = (bool) data_get($settings, 'gifts_show_claimers_public', false);

        $invitationCode = (string) $request->query('invitation_code', '');
        $guest = null;

        if ($requireCode && $invitationCode !== '') {
            $guest = Guest::query()
                ->where('event_id', $event->id)
                ->where('invitation_code', $invitationCode)
                ->first();
        }

        $gifts = EventGift::query()
            ->where('event_id', $event->id)
            ->orderBy('display_order')
            ->get();

        $myClaimsByGiftId = collect();
        if ($guest) {
            $myClaimsByGiftId = EventGiftClaim::query()
                ->where('event_id', $event->id)
                ->where('guest_id', $guest->id)
                ->whereIn('status', [EventGiftClaim::STATUS_RESERVED, EventGiftClaim::STATUS_PURCHASED])
                ->get()
                ->keyBy('gift_id');
        }

        $claimersByGiftId = collect();
        if ($showClaimers) {
            $giftIds = $gifts->pluck('id')->all();

            $claims = EventGiftClaim::query()
                ->whereIn('gift_id', $giftIds)
                ->whereIn('status', [EventGiftClaim::STATUS_RESERVED, EventGiftClaim::STATUS_PURCHASED])
                ->get();

            $guestIds = $claims->pluck('guest_id')->unique()->values()->all();
            $guestsById = Guest::query()->whereIn('id', $guestIds)->get()->keyBy('id');

            $claimersByGiftId = $claims->groupBy('gift_id')->map(function ($rows) use ($guestsById) {
                return $rows->map(function (EventGiftClaim $c) use ($guestsById) {
                    $g = $guestsById->get($c->guest_id);
                    return [
                        'guest_id' => $c->guest_id,
                        'name'     => $this->guestDisplayName($g),
                        'quantity' => (int) $c->quantity,
                        'status'   => $c->status,
                    ];
                })->values();
            });
        }

        return response()->json([
            'event_id' => $event->id,
            'gifts' => $gifts->map(function (EventGift $gift) use ($myClaimsByGiftId, $claimersByGiftId) {
                $total = max(1, (int) ($gift->quantity ?? 1));
                $reserved = max(0, (int) ($gift->quantity_reserved ?? 0));
                $available = max(0, $total - $reserved);

                $myQty = 0;
                $myClaim = $myClaimsByGiftId->get($gift->id);
                if ($myClaim) $myQty = (int) $myClaim->quantity;

                return [
                    'gift_id'           => $gift->id,
                    'status'            => $gift->status,
                    'quantity'          => $total,
                    'quantity_reserved' => $reserved,
                    'available_units'   => $available,
                    'my_claim_quantity' => $myQty,
                    'claimers'          => $claimersByGiftId->get($gift->id, collect())->all(),
                ];
            })->values(),
        ], 200);
    }

    public function reserve(string $slug, int $gift, Request $request)
    {
        $event = Event::publicVisible()->where('slug', $slug)->firstOrFail();

        if (!data_get($event->modules, 'gifts')) {
            abort(404);
        }

        $settings = $event->settings ?? [];

        $requireCode  = (bool) data_get($settings, 'gifts_require_invitation_code', true);
        $allowMulti   = (bool) data_get($settings, 'gifts_allow_multi_unit_reserve', false);
        $showClaimers = (bool) data_get($settings, 'gifts_show_claimers_public', false);

        $maxUnitsPerGuestPerGift = max(1, (int) data_get($settings, 'gifts_max_units_per_guest_per_gift', 1));

        $rules = [
            'invitation_code' => $requireCode ? ['required', 'string', 'max:64'] : ['nullable', 'string', 'max:64'],
        ];
        if ($allowMulti) {
            $rules['quantity'] = ['required', 'integer', 'min:1', 'max:999'];
        }

        $data = $request->validate($rules);

        $guest = null;
        if ($requireCode) {
            $guest = Guest::query()
                ->where('event_id', $event->id)
                ->where('invitation_code', $data['invitation_code'])
                ->first();

            if (!$guest) {
                return $this->errorResponse(
                    $request,
                    'No pudimos identificar su invitación. Use el enlace personal que recibió.',
                    422
                );
            }
        }

        $giftModel = null;

        DB::transaction(function () use (
            $event,
            $guest,
            $gift,
            $allowMulti,
            $data,
            $request,
            $maxUnitsPerGuestPerGift,
            &$giftModel
        ) {
            $giftModel = EventGift::query()
                ->where('event_id', $event->id)
                ->where('id', $gift)
                ->lockForUpdate()
                ->firstOrFail();

            if ($giftModel->status === EventGift::STATUS_PURCHASED) {
                $this->abortBusiness($request, 'Este regalo ya fue confirmado como comprado.', 422);
            }

            $total = max(1, (int) ($giftModel->quantity ?? 1));
            $reserved = max(0, (int) ($giftModel->quantity_reserved ?? 0));
            $available = max(0, $total - $reserved);

            if ($available <= 0) {
                $this->abortBusiness($request, 'Este regalo ya no tiene unidades disponibles.', 422);
            }

            $reserveQty = $allowMulti ? (int) ($data['quantity'] ?? 1) : 1;
            if ($reserveQty > $available) {
                $this->abortBusiness($request, 'Este regalo ya no tiene unidades suficientes disponibles.', 422);
            }

            if ($guest) {
                $claim = EventGiftClaim::query()
                    ->where('event_id', $event->id)
                    ->where('gift_id', $giftModel->id)
                    ->where('guest_id', $guest->id)
                    ->lockForUpdate()
                    ->first();

                $activeQty = 0;
                if ($claim && in_array($claim->status, [EventGiftClaim::STATUS_RESERVED, EventGiftClaim::STATUS_PURCHASED], true)) {
                    $activeQty = (int) $claim->quantity;
                }

                $remainingForGuest = max(0, $maxUnitsPerGuestPerGift - $activeQty);

                if ($remainingForGuest <= 0) {
                    $this->abortBusiness($request, 'Ya te comprometiste con este regalo.', 422);
                }

                if ($reserveQty > $remainingForGuest) {
                    $this->abortBusiness(
                        $request,
                        'No puedes apartar tantas unidades. Límite por invitado: ' . $maxUnitsPerGuestPerGift . '.',
                        422
                    );
                }

                if (!$claim) {
                    $claim = new EventGiftClaim([
                        'event_id' => $event->id,
                        'gift_id'  => $giftModel->id,
                        'guest_id' => $guest->id,
                        'quantity' => 0,
                        'status'   => EventGiftClaim::STATUS_RESERVED,
                    ]);
                }

                $claim->quantity = $activeQty + $reserveQty;
                $claim->status   = EventGiftClaim::STATUS_RESERVED;
                $claim->save();
            }

            $this->recalculateGiftReservedAndOwner($giftModel);
        });

        $payload = $this->buildGiftPayload($event, $giftModel, $guest, $showClaimers, $maxUnitsPerGuestPerGift);

        // ✅ LOG: acción exitosa (no afecta lógica, solo observabilidad)
        Log::channel('gifts')->info('gift_reserved', [
            'event_id' => $event->id,
            'event_slug' => $event->slug,
            'gift_id' => $giftModel?->id,
            'gift_name' => $giftModel?->name,
            'guest_id' => $guest?->id,
            'requested_qty' => $allowMulti ? (int) ($request->input('quantity', 1)) : 1,
            'final_my_qty' => (int) ($payload['my_claim_quantity'] ?? 0),
            'ip' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
        ]);

        return response()->json($payload, 200);
    }

    public function unreserve(string $slug, int $gift, Request $request)
    {
        $event = Event::publicVisible()->where('slug', $slug)->firstOrFail();

        if (!data_get($event->modules, 'gifts')) {
            abort(404);
        }

        $settings = $event->settings ?? [];

        $allowUnclaim = (bool) data_get($settings, 'gifts_allow_unclaim', false);
        if (!$allowUnclaim) {
            abort(404);
        }

        $requireCode  = (bool) data_get($settings, 'gifts_require_invitation_code', true);
        $showClaimers = (bool) data_get($settings, 'gifts_show_claimers_public', false);

        $maxUnitsPerGuestPerGift = max(1, (int) data_get($settings, 'gifts_max_units_per_guest_per_gift', 1));

        $data = $request->validate([
            'invitation_code' => ['required', 'string', 'max:64'],
        ]);

        $guest = null;
        if ($requireCode) {
            $guest = Guest::query()
                ->where('event_id', $event->id)
                ->where('invitation_code', $data['invitation_code'])
                ->first();

            if (!$guest) {
                return $this->errorResponse(
                    $request,
                    'No pudimos identificar su invitación. Use el enlace personal que recibió.',
                    422
                );
            }
        }

        $giftModel = null;

        DB::transaction(function () use ($event, $guest, $gift, $request, &$giftModel) {
            $giftModel = EventGift::query()
                ->where('event_id', $event->id)
                ->where('id', $gift)
                ->lockForUpdate()
                ->firstOrFail();

            if ($giftModel->status === EventGift::STATUS_PURCHASED) {
                $this->abortBusiness($request, 'Este regalo ya fue confirmado como comprado.', 422);
            }

            $claim = EventGiftClaim::query()
                ->where('event_id', $event->id)
                ->where('gift_id', $giftModel->id)
                ->where('guest_id', $guest->id)
                ->where('status', EventGiftClaim::STATUS_RESERVED)
                ->lockForUpdate()
                ->first();

            if (!$claim) {
                $purchasedClaimExists = EventGiftClaim::query()
                    ->where('event_id', $event->id)
                    ->where('gift_id', $giftModel->id)
                    ->where('guest_id', $guest->id)
                    ->where('status', EventGiftClaim::STATUS_PURCHASED)
                    ->exists();

                if ($purchasedClaimExists) {
                    $this->abortBusiness($request, 'Este regalo ya fue confirmado como comprado.', 422);
                }

                $this->abortBusiness($request, 'No tienes este regalo apartado.', 422);
            }

            $claim->status = EventGiftClaim::STATUS_CANCELLED;
            $claim->quantity = 0;
            $claim->save();

            $this->recalculateGiftReservedAndOwner($giftModel);
        });

        $payload = $this->buildGiftPayload($event, $giftModel, $guest, $showClaimers, $maxUnitsPerGuestPerGift);
        $payload['message'] = 'Reserva liberada correctamente.';

        // ✅ LOG: acción exitosa
        Log::channel('gifts')->info('gift_unreserved', [
            'event_id' => $event->id,
            'event_slug' => $event->slug,
            'gift_id' => $giftModel?->id,
            'gift_name' => $giftModel?->name,
            'guest_id' => $guest?->id,
            'final_my_qty' => (int) ($payload['my_claim_quantity'] ?? 0),
            'ip' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
        ]);

        return response()->json($payload, 200);
    }

    public function myClaims(string $slug, Request $request)
    {
        $event = Event::publicVisible()->where('slug', $slug)->firstOrFail();

        if (!data_get($event->modules, 'gifts')) {
            abort(404);
        }

        $settings = $event->settings ?? [];
        $requireCode = (bool) data_get($settings, 'gifts_require_invitation_code', true);

        $invitationCode = (string) $request->query('invitation_code', '');
        if (!$requireCode || $invitationCode === '') {
            return response()->json(['claims' => []], 200);
        }

        $guest = Guest::query()
            ->where('event_id', $event->id)
            ->where('invitation_code', $invitationCode)
            ->first();

        if (!$guest) {
            return response()->json(['claims' => []], 200);
        }

        $claims = EventGiftClaim::query()
            ->where('event_id', $event->id)
            ->where('guest_id', $guest->id)
            ->whereIn('status', [EventGiftClaim::STATUS_RESERVED, EventGiftClaim::STATUS_PURCHASED])
            ->get()
            ->mapWithKeys(fn (EventGiftClaim $c) => [(string) $c->gift_id => (int) $c->quantity]);

        return response()->json(['claims' => $claims], 200);
    }

    // ---------------- Helpers ----------------

    protected function recalculateGiftReservedAndOwner(EventGift $gift): void
    {
        $totalReserved = EventGiftClaim::query()
            ->where('gift_id', $gift->id)
            ->whereIn('status', [EventGiftClaim::STATUS_RESERVED, EventGiftClaim::STATUS_PURCHASED])
            ->sum('quantity');

        $gift->quantity_reserved = (int) $totalReserved;

        if ($gift->status !== EventGift::STATUS_PURCHASED) {
            $gift->status = $gift->quantity_reserved > 0 ? EventGift::STATUS_RESERVED : EventGift::STATUS_PENDING;
        }

        $activeGuestIds = EventGiftClaim::query()
            ->where('gift_id', $gift->id)
            ->whereIn('status', [EventGiftClaim::STATUS_RESERVED, EventGiftClaim::STATUS_PURCHASED])
            ->where('quantity', '>', 0)
            ->distinct()
            ->pluck('guest_id');

        $gift->claimed_by_guest_id = $activeGuestIds->count() === 1 ? $activeGuestIds->first() : null;

        $gift->save();
    }

    protected function buildGiftPayload(Event $event, EventGift $gift, ?Guest $guest, bool $showClaimers, int $maxUnitsPerGuest): array
    {
        $total = max(1, (int) ($gift->quantity ?? 1));
        $reserved = max(0, (int) ($gift->quantity_reserved ?? 0));
        $available = max(0, $total - $reserved);

        $myQty = 0;
        if ($guest) {
            $myQty = (int) EventGiftClaim::query()
                ->where('event_id', $event->id)
                ->where('gift_id', $gift->id)
                ->where('guest_id', $guest->id)
                ->whereIn('status', [EventGiftClaim::STATUS_RESERVED, EventGiftClaim::STATUS_PURCHASED])
                ->sum('quantity');
        }

        $claimers = [];
        if ($showClaimers) {
            $claims = EventGiftClaim::query()
                ->where('gift_id', $gift->id)
                ->whereIn('status', [EventGiftClaim::STATUS_RESERVED, EventGiftClaim::STATUS_PURCHASED])
                ->get();

            $guestIds = $claims->pluck('guest_id')->unique()->values()->all();
            $guestsById = Guest::query()->whereIn('id', $guestIds)->get()->keyBy('id');

            $claimers = $claims->map(function (EventGiftClaim $c) use ($guestsById) {
                $g = $guestsById->get($c->guest_id);
                return [
                    'guest_id' => $c->guest_id,
                    'name'     => $this->guestDisplayName($g),
                    'quantity' => (int) $c->quantity,
                    'status'   => $c->status,
                ];
            })->values()->all();
        }

        return [
            'message'             => 'Regalo apartado correctamente.',
            'gift_id'             => $gift->id,
            'status'              => $gift->status,
            'quantity'            => $total,
            'quantity_reserved'   => $reserved,
            'available_units'     => $available,
            'my_claim_quantity'   => $myQty,
            'max_units_per_guest' => max(1, $maxUnitsPerGuest),
            'claimers'            => $claimers,
        ];
    }

    protected function guestDisplayName($guest): string
    {
        if (!$guest) return 'Invitado';
        $name = trim((string) data_get($guest, 'name', ''));
        return $name !== '' ? $name : 'Invitado';
    }

    protected function abortBusiness(Request $request, string $message, int $statusCode): never
    {
        throw new HttpResponseException(
            response()->json(['message' => $message], $statusCode)
        );
    }

    protected function errorResponse(Request $request, string $message, int $statusCode)
    {
        return response()->json(['message' => $message], $statusCode);
    }
}
