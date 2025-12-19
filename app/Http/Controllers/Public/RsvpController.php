<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Guest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
     *             @OA\Property(property="show_in_public_list", type="boolean", example=true),
     *             @OA\Property(property="dietary_tags", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="dietary_notes", type="string", example="Alergia fuerte al cacahuate")
     *         )
     *     ),
     *     @OA\Response(
     *         response=302,
     *         description="Redirección de vuelta a la página del evento con mensaje de éxito o error"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Evento no encontrado, no visible públicamente o módulo de RSVP desactivado."
     *     )
     * )
     */
    public function store(string $slug, Request $request): JsonResponse|RedirectResponse
    {
        $event = Event::publicVisible()
            ->where('slug', $slug)
            ->firstOrFail();

        if (! $event->isModuleEnabled('rsvp')) {
            abort(404);
        }

        // ✅ Lista blanca de tags permitidos (debe coincidir con tu Blade)
        $allowedDietTags = [
            'vegano',
            'vegetariano',
            'sin_gluten',
            'diabetico',
            'sin_lactosa',
            'alergia_nueces',
        ];

        $validated = $request->validate([
            'invitation_code'     => ['required', 'string'],
            'rsvp_status'         => ['required', 'in:yes,no,maybe'],
            'guests_confirmed'    => ['nullable', 'integer', 'min:1', 'max:20', 'required_if:rsvp_status,yes,maybe'],
            'rsvp_message'        => ['nullable', 'string', 'max:1000'],
            'show_in_public_list' => ['sometimes', 'boolean'],

            // ✅ NUEVO
            'dietary_tags'        => ['nullable', 'array'],
            'dietary_tags.*'      => ['string', Rule::in($allowedDietTags)],
            'dietary_notes'       => ['nullable', 'string', 'max:1000'],
        ]);

        $guest = Guest::query()
            ->where('event_id', $event->id)
            ->where('invitation_code', $validated['invitation_code'])
            ->first();

        if (! $guest) {
            return redirect()
                ->route('events.show', ['slug' => $event->slug, 'i' => $validated['invitation_code']])
                ->with('rsvp_error', 'No encontramos una invitación asociada a este código. Verifique el enlace o contacte a los organizadores.');
        }

        // ✅ Detectar si es primera vez (para el mensaje)
        $wasFirstResponse = ($guest->rsvp_status === Guest::RSVP_PENDING);

        if (in_array($validated['rsvp_status'], [Guest::RSVP_YES, Guest::RSVP_MAYBE], true)) {
            $maxSeats = (int) ($guest->invited_seats ?? 0);

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

        $guest->rsvp_status = $validated['rsvp_status'];

        if ($validated['rsvp_status'] === Guest::RSVP_NO) {
            $guest->guests_confirmed = 0;
        } else {
            $guest->guests_confirmed = $validated['guests_confirmed'] ?? 1;
        }

        $guest->rsvp_message        = $validated['rsvp_message'] ?? null;
        $guest->show_in_public_list = $request->boolean('show_in_public_list');

        // ✅ NUEVO: guardar dieta/alergias (y permitir “limpiar” si desmarcan todo)
        $dietTags = $request->input('dietary_tags', []);
        $dietTags = is_array($dietTags) ? array_values(array_unique($dietTags)) : [];
        $guest->dietary_tags  = $dietTags;
        $guest->dietary_notes = $validated['dietary_notes'] ?? null;

        $guest->save();

        $successMessage = $wasFirstResponse
            ? '¡Gracias! Tu asistencia ha sido registrada correctamente.'
            : '¡Listo! Se ha actualizado el estado de tu asistencia.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $successMessage,
                'guest' => [
                    'name'               => $guest->name,
                    'invitation_code'    => $guest->invitation_code,
                    'rsvp_status'        => $guest->rsvp_status,
                    'guests_confirmed'   => (int) ($guest->guests_confirmed ?? 0),
                    'rsvp_message'       => $guest->rsvp_message,
                    'show_in_public_list'=> (bool) $guest->show_in_public_list,

                    // útil para UI sin recarga
                    'dietary_tags'       => $guest->dietary_tags ?? [],
                    'dietary_notes'      => $guest->dietary_notes,
                ],
            ]);
        }

        return redirect()
            ->route('events.show', ['slug' => $event->slug, 'i' => $guest->invitation_code])
            ->with('rsvp_success', $successMessage);
    }
}
