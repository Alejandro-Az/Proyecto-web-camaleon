<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventGift;
use App\Models\EventGiftClaim;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventGiftAdminController extends Controller
{
    /**
     * @OA\Post(
     *   path="/admin/eventos/{event}/regalos/{gift}/marcar-comprado",
     *   tags={"Admin Regalos"},
     *   summary="Marca un regalo como comprado",
     *   description="Marca el regalo como comprado, actualiza claims reservados y recalcula cantidades reservadas.",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(
     *     name="event",
     *     in="path",
     *     required=true,
     *     description="ID del evento",
     *     @OA\Schema(type="integer", example=1)
     *   ),
     *   @OA\Parameter(
     *     name="gift",
     *     in="path",
     *     required=true,
     *     description="ID del regalo",
     *     @OA\Schema(type="integer", example=10)
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Regalo marcado como comprado",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Regalo marcado como comprado."),
     *       @OA\Property(property="gift_id", type="integer", example=10),
     *       @OA\Property(property="status", type="string", example="purchased")
     *     )
     *   ),
     *   @OA\Response(response=401, description="No autenticado"),
     *   @OA\Response(response=403, description="Sin permisos"),
     *   @OA\Response(response=404, description="Evento o regalo no encontrado")
     * )
     */
    public function markPurchased(Event $event, int $gift, Request $request)
    {
        if (!data_get($event->modules, 'gifts')) {
            abort(404);
        }

        $giftModel = null;

        DB::transaction(function () use ($event, $gift, &$giftModel) {
            $giftModel = EventGift::query()
                ->where('event_id', $event->id)
                ->where('id', $gift)
                ->lockForUpdate()
                ->firstOrFail();

            $giftModel->status = EventGift::STATUS_PURCHASED;
            $giftModel->save();

            // Congelamos claims reservados como purchased
            EventGiftClaim::query()
                ->where('event_id', $event->id)
                ->where('gift_id', $giftModel->id)
                ->where('status', EventGiftClaim::STATUS_RESERVED)
                ->update(['status' => EventGiftClaim::STATUS_PURCHASED]);

            // Recalcular reserved
            $totalReserved = EventGiftClaim::query()
                ->where('gift_id', $giftModel->id)
                ->whereIn('status', [EventGiftClaim::STATUS_RESERVED, EventGiftClaim::STATUS_PURCHASED])
                ->sum('quantity');

            $giftModel->quantity_reserved = (int) $totalReserved;

            // claimed_by_guest_id consistente (1 => id, varios => null)
            $activeGuestIds = EventGiftClaim::query()
                ->where('gift_id', $giftModel->id)
                ->whereIn('status', [EventGiftClaim::STATUS_RESERVED, EventGiftClaim::STATUS_PURCHASED])
                ->where('quantity', '>', 0)
                ->distinct()
                ->pluck('guest_id');

            $giftModel->claimed_by_guest_id = $activeGuestIds->count() === 1 ? $activeGuestIds->first() : null;

            $giftModel->save();
        });

        return response()->json([
            'message' => 'Regalo marcado como comprado.',
            'gift_id' => $giftModel->id,
            'status'  => $giftModel->status,
        ], 200);
    }
}
