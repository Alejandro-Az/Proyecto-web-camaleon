<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Guest;
use App\Models\EventPhoto;
use App\Models\EventSong;
use App\Models\SongVote;
use Illuminate\Http\Request;

class EventController extends Controller
{
/**
     * @OA\Get(
     *     path="/eventos/{slug}",
     *     tags={"Eventos Públicos"},
     *     summary="Ver la página pública de un evento",
     *     description="Devuelve la página HTML pública de un evento identificado por su slug. La página puede incluir módulos como galería de fotos, RSVP, lista pública de asistentes, sugerencia de canciones y votos, dependiendo de la configuración del evento.",
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
                'songs' => function ($query) {
                    $query->approved()
                          ->orderByDesc('votes_count');
                },
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

        $galleryPhotos = EventPhoto::query()
            ->where('event_id', $event->id)
            ->ofType(EventPhoto::TYPE_GALLERY)
            ->approved()
            ->orderBy('display_order')
            ->orderBy('id')
            ->get();

        return view('events.show', [
            'event'                     => $event,
            'guest'                     => $guest,
            'confirmedGuests'           => $confirmedGuests,
            'rsvpEditMode'              => $rsvpEditMode,
            'guestSongSuggestionsCount' => $guestSongSuggestionsCount,
            'guestVotesCount'           => $guestVotesCount,
            'votedSongIds'              => $votedSongIds,
            'galleryPhotos'             => $galleryPhotos,
        ]);
    }
}
