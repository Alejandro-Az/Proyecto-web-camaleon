<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Guest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Controlador público para gestionar la confirmación de asistencia (RSVP)
 * de los invitados a un evento.
 */
class RsvpController extends Controller
{
    /**
     * @OA\Post(
     *     path="/eventos/{slug}/rsvp",
     *     tags={"RSVP"},
     *     summary="Registrar/actualizar la confirmación de asistencia de un invitado",
     *     description="Actualiza el RSVP de un invitado identificado por su código de invitación para un evento público.",
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Slug del evento (por ejemplo, boda-prueba-ana-luis)",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"invitation_code","rsvp_status"},
     *             @OA\Property(property="invitation_code", type="string", example="DEMO1234"),
     *             @OA\Property(property="rsvp_status", type="string", enum={"yes","no","maybe"}, example="yes"),
     *             @OA\Property(property="guests_confirmed", type="integer", example=2),
     *             @OA\Property(property="rsvp_message", type="string", example="¡Nos vemos ahí!"),
     *             @OA\Property(property="show_in_public_list", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=302,
     *         description="Redirección de vuelta a la página del evento con mensaje de éxito o error"
     *     )
     * )
     */
    public function store(string $slug, Request $request): RedirectResponse
    {
        // Buscar evento visible
        $event = Event::publicVisible()
            ->where('slug', $slug)
            ->firstOrFail();

        // Validar datos de la solicitud (validación general)
        $validated = $request->validate([
            'invitation_code'     => ['required', 'string'],
            'rsvp_status'         => ['required', 'in:yes,no,maybe'],
            'guests_confirmed'    => ['nullable', 'integer', 'min:1', 'max:20', 'required_if:rsvp_status,yes,maybe'],
            'rsvp_message'        => ['nullable', 'string', 'max:1000'],
            'show_in_public_list' => ['sometimes', 'boolean'],
        ]);

        // Buscar al invitado por código de invitación
        $guest = Guest::query()
            ->where('event_id', $event->id)
            ->where('invitation_code', $validated['invitation_code'])
            ->first();

        if (! $guest) {
            return redirect()
                ->route('events.show', ['slug' => $event->slug, 'i' => $validated['invitation_code']])
                ->with('rsvp_error', 'No encontramos una invitación asociada a este código. Verifique el enlace o contacte a los organizadores.');
        }

        // Si va a asistir (o tal vez), validar contra invited_seats
        if (in_array($validated['rsvp_status'], [Guest::RSVP_YES, Guest::RSVP_MAYBE], true)) {
            $maxSeats = (int) ($guest->invited_seats ?? 0);

            // Si tenemos un número de asientos definidos (>0), lo usamos como tope
            if ($maxSeats > 0) {
                $requestedSeats = (int) ($validated['guests_confirmed'] ?? 1);

                if ($requestedSeats > $maxSeats) {
                    return redirect()
                        ->route('events.show', [
                            'slug' => $event->slug,
                            'i'    => $guest->invitation_code,
                            'edit' => 1,
                        ])
                        ->withErrors([
                            'guests_confirmed' => "Con esta invitación puede confirmar hasta {$maxSeats} persona(s).",
                        ])
                        ->withInput();
                }
            }
        }

        // Actualizar RSVP del invitado
        $guest->rsvp_status = $validated['rsvp_status'];

        if ($validated['rsvp_status'] === Guest::RSVP_NO) {
            // Si NO asiste, siempre dejamos 0 personas
            $guest->guests_confirmed = 0;
        } else {
            // Si SÍ asiste o MAYBE, usamos el valor enviado (o 1 por defecto)
            $guest->guests_confirmed = $validated['guests_confirmed'] ?? 1;
        }

        $guest->rsvp_message = $validated['rsvp_message'] ?? null;

        // Checkbox de mostrar en lista pública
        $guest->show_in_public_list = $request->boolean('show_in_public_list');

        $guest->save();

        return redirect()
            ->route('events.show', ['slug' => $event->slug, 'i' => $guest->invitation_code])
            ->with('rsvp_success', '¡Gracias! Tu asistencia ha sido registrada correctamente.');
    }
}
