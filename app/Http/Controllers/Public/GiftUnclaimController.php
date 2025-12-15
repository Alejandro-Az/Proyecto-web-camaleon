<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventGift;
use App\Models\EventGiftClaim;
use App\Models\Guest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GiftUnclaimController extends Controller
{
    /**
     * @OA\Post(
     *     path="/eventos/{slug}/regalos/{gift}/liberar",
     *     tags={"Regalos Públicos"},
     *     summary="Liberar (cancelar) la reserva de un regalo",
     *     description="Permite que un invitado libere la reserva de un regalo que previamente había apartado.",
     *     operationId="publicUnreserveGift",
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Slug del evento",
     *         @OA\Schema(type="string", example="boda-prueba-ana-luis")
     *     ),
     *     @OA\Parameter(
     *         name="gift",
     *         in="path",
     *         required=true,
     *         description="ID del regalo dentro del evento",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"invitation_code"},
     *             @OA\Property(
     *                 property="invitation_code",
     *                 type="string",
     *                 example="DEMO1234",
     *                 description="Código de invitación del invitado que quiere liberar el regalo"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reserva liberada correctamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="gift_id", type="integer", example=1),
     *             @OA\Property(property="quantity_reserved", type="integer", example=0),
     *             @OA\Property(property="status", type="string", example="pending")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Evento no público, módulo de regalos desactivado o liberación deshabilitada."
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Errores de validación o reglas de negocio (invitación inválida, regalo no reservado, ya comprado, etc.)"
     *     )
     * )
     */
     public function __invoke(string $slug, EventGift $gift, Request $request)
    {
        // 1) Evento público
        $event = Event::publicVisible()
            ->where('slug', $slug)
            ->firstOrFail();

        if ($gift->event_id !== $event->id) {
            abort(404);
        }

        // 2) Módulo activo
        $modules = $event->modules ?? [];
        if (! data_get($modules, 'gifts', false)) {
            abort(404);
        }

        // 3) Config: ¿se permite unclaim?
        $settings     = $event->settings ?? [];
        $allowUnclaim = (bool) data_get($settings, 'gifts_allow_unclaim', false);

        if (! $allowUnclaim) {
            abort(404);
        }

        // 4) Validación
        $data = $request->validate([
            'invitation_code' => ['required', 'string'],
        ]);

        // 5) Invitado
        $guest = Guest::query()
            ->where('event_id', $event->id)
            ->where('invitation_code', $data['invitation_code'])
            ->first();

        if (! $guest) {
            $message = 'No pudimos identificar su invitación. Use el enlace personal que recibió.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return redirect()
                ->back()
                ->with('gifts_error', $message)
                ->withInput();
        }

        // 6) Reglas de negocio
        if ($gift->status === EventGift::STATUS_PURCHASED) {
            $message = 'Este regalo ya fue confirmado como comprado.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return redirect()
                ->back()
                ->with('gifts_error', $message)
                ->withInput();
        }

        // Claim reservado para este invitado
        $claim = EventGiftClaim::query()
            ->where('event_id', $event->id)
            ->where('gift_id', $gift->id)
            ->where('guest_id', $guest->id)
            ->where('status', EventGiftClaim::STATUS_RESERVED)
            ->first();

        if (! $claim) {
            $purchasedClaimExists = EventGiftClaim::query()
                ->where('event_id', $event->id)
                ->where('gift_id', $gift->id)
                ->where('guest_id', $guest->id)
                ->where('status', EventGiftClaim::STATUS_PURCHASED)
                ->exists();

            $message = $purchasedClaimExists
                ? 'Este regalo ya fue confirmado como comprado.'
                : 'No tienes este regalo apartado.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return redirect()
                ->back()
                ->with('gifts_error', $message)
                ->withInput();
        }

        // 7) Liberar en transacción
        DB::transaction(function () use ($gift, $claim) {
            $gift->quantity_reserved = max(0, $gift->quantity_reserved - $claim->quantity);

            if ($gift->quantity_reserved <= 0 && $gift->status !== EventGift::STATUS_PURCHASED) {
                $gift->status = EventGift::STATUS_PENDING;
            }

            $gift->save();

            $claim->status = EventGiftClaim::STATUS_CANCELLED;
            $claim->save();
        });

        $gift->refresh();

        if ($request->expectsJson()) {
            return response()->json([
                'gift_id'           => $gift->id,
                'quantity_reserved' => $gift->quantity_reserved,
                'status'            => $gift->status,
            ], 200);
        }

        return redirect()
            ->back()
            ->with('gifts_success', 'Hemos liberado su reserva de este regalo.');
    }

}
