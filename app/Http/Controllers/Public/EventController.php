<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Guest;
use App\Models\EventPhoto;
use App\Models\SongVote;
use App\Models\EventGift;
use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * @OA\Get(
     *     path="/eventos/{slug}",
     *     tags={"Eventos Públicos"},
     *     summary="Ver la página pública de un evento",
     *     description="Devuelve la página HTML pública de un evento identificado por su slug. La página puede incluir módulos como portada (hero), galería de fotos, itinerario del evento, RSVP, lista pública de asistentes, sugerencia de canciones y votos, así como fotos subidas por invitados, dependiendo de la configuración del evento.",
     *     operationId="publicShowEvent",
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Slug del evento (por ejemplo, boda-prueba-ana-luis)",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Página HTML con la información pública del evento y sus módulos activos."
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Evento no encontrado o no visible públicamente."
     *     )
     * )
     */
    public function show(string $slug, Request $request)
    {
        $event = Event::publicVisible()
            ->where('slug', $slug)
            ->with([
                'locations' => function ($query) {
                    $query->orderBy('display_order');
                },
                'dressCodes' => function ($query) {
                    $query->where('is_enabled', true)
                        ->orderBy('display_order')
                        ->orderBy('id');
                },
                'romanticPhrases' => function ($query) {
                    $query->where('is_enabled', true)
                        ->orderBy('display_order')
                        ->orderBy('id');
                },
                'songs' => function ($query) {
                    $query->approved()
                        ->orderByDesc('votes_count');
                },
                'schedules' => function ($query) {
                    $query->orderBy('starts_at')
                        ->orderBy('display_order')
                        ->orderBy('id');
                },

                // ✅ NUEVO: Historia / Sobre...
                'stories' => function ($query) {
                    $query->where('is_enabled', true)
                        ->orderBy('display_order')
                        ->orderBy('id');
                },
                'stories.examplePhoto',
            ])
            ->firstOrFail();

        // Invitado vía código (?i=CODIGO)
        $guest = null;
        $invitationCode = $request->query('i');

        if ($invitationCode) {
            $guest = Guest::query()
                ->where('event_id', $event->id)
                ->where('invitation_code', $invitationCode)
                ->first();
        }

        // Lista pública de asistentes confirmados
        $confirmedGuests = $event->guests()
            ->confirmed()
            ->where('show_in_public_list', true)
            ->orderBy('name')
            ->get();

        // Modo edición de RSVP (?edit=1)
        $rsvpEditMode = $request->boolean('edit');

        // Estadísticas para playlist por invitado
        $guestSongSuggestionsCount = null;
        $guestVotesCount           = null;
        $votedSongIds              = [];

        if ($guest) {
            $guestSongSuggestionsCount = $event->songs()
                ->where('suggested_by_guest_id', $guest->id)
                ->count();

            $guestVotesCount = $event->songVotes()
                ->where('guest_id', $guest->id)
                ->count();

            if ($event->songs->isNotEmpty()) {
                $votedSongIds = SongVote::query()
                    ->where('event_id', $event->id)
                    ->where('guest_id', $guest->id)
                    ->whereIn('song_id', $event->songs->pluck('id'))
                    ->pluck('song_id')
                    ->all();
            }
        }

        // Foto de portada (hero), si existe
        $heroPhoto = EventPhoto::query()
            ->where('event_id', $event->id)
            ->where('type', 'hero')
            ->approved()
            ->orderBy('display_order')
            ->orderBy('id')
            ->first();

        // Fotos de galería
        $galleryPhotos = EventPhoto::query()
            ->where('event_id', $event->id)
            ->where('type', 'gallery')
            ->approved()
            ->orderBy('display_order')
            ->orderBy('id')
            ->get();

        // Regalos del evento (mesa de regalos)
        $gifts = collect();
        if ($event->isModuleEnabled('gifts')) {
            $hidePurchased = (bool) data_get(
                $event->settings,
                'gifts_hide_purchased_from_public',
                false
            );

            $gifts = EventGift::publicList($event, $hidePurchased)->get();
        }

        // Fotos de invitados (solo las aprobadas)
        $guestPhotos = EventPhoto::query()
            ->where('event_id', $event->id)
            ->where('type', 'guest_upload')
            ->approved()
            ->orderBy('display_order')
            ->orderBy('id')
            ->get();

        // Para evitar ruido en la vista (aunque use ??)
        $guestGiftClaimsByGiftId = collect();

        return view('events.show', [
            'event'                     => $event,
            'guest'                     => $guest,
            'confirmedGuests'           => $confirmedGuests,
            'rsvpEditMode'              => $rsvpEditMode,
            'guestSongSuggestionsCount' => $guestSongSuggestionsCount,
            'guestVotesCount'           => $guestVotesCount,
            'votedSongIds'              => $votedSongIds,
            'heroPhoto'                 => $heroPhoto,
            'galleryPhotos'             => $galleryPhotos,
            'guestPhotos'               => $guestPhotos,
            'gifts'                     => $gifts,
            'guestGiftClaimsByGiftId'   => $guestGiftClaimsByGiftId,
        ]);
    }
}
