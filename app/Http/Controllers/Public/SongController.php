<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventSong;
use App\Models\Guest;
use App\Models\SongVote;
use Illuminate\Http\Request;

/**
 * Controlador público para sugerencia de canciones y votos
 * en la playlist de un evento.
 */
class SongController extends Controller
{
    /**
     * Registrar una nueva canción sugerida.
     *
     * @OA\Post(
     *     path="/eventos/{slug}/canciones",
     *     tags={"Eventos Públicos"},
     *     summary="Sugerir una canción para el evento",
     *     description="Registra una nueva canción sugerida para la playlist del evento. 
     *                  Soporta respuestas HTML (redirect) y JSON (AJAX) según el encabezado Accept.",
     *     operationId="publicSuggestSong",
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
     *             required={"invitation_code","title"},
     *             @OA\Property(property="invitation_code", type="string", example="DEMO1234"),
     *             @OA\Property(property="title", type="string", example="Perfect"),
     *             @OA\Property(property="artist", type="string", example="Ed Sheeran"),
     *             @OA\Property(property="url", type="string", example="https://open.spotify.com/track/..."),
     *             @OA\Property(property="message_for_couple", type="string", example="Para el primer baile"),
     *             @OA\Property(property="show_author", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Canción sugerida correctamente (respuesta JSON cuando se usa AJAX)."
     *     ),
     *     @OA\Response(
     *         response=302,
     *         description="Redirección a la página del evento con mensaje flash de éxito o error (uso tradicional de formulario HTML)."
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación, invitación no encontrada, límite alcanzado o canción duplicada."
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Evento no encontrado, no visible públicamente o módulo de canciones desactivado."
     *     )
     * )
     */
    public function store(string $slug, Request $request)
    {
        $event = Event::publicVisible()
            ->where('slug', $slug)
            ->firstOrFail();

        // Módulo activo
        if (! data_get($event->modules, 'songs')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'El módulo de canciones no está disponible para este evento.',
                ], 404);
            }

            abort(404);
        }

        // Invitado por código
        $invitationCode = $request->input('invitation_code');

        $guest = Guest::query()
            ->where('event_id', $event->id)
            ->where('invitation_code', $invitationCode)
            ->first();

        if (! $guest) {
            $message = 'No pudimos identificar su invitación. Use el enlace personal que recibió.';

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                ], 422);
            }

            return redirect()
                ->route('events.show', ['slug' => $event->slug, 'i' => $invitationCode])
                ->with('song_error', $message);
        }

        $validated = $request->validate([
            'title'              => ['required', 'string', 'max:150'],
            'artist'             => ['nullable', 'string', 'max:150'],
            'url'                => ['nullable', 'url', 'max:255'],
            'message_for_couple' => ['nullable', 'string', 'max:500'],
            'show_author'        => ['sometimes', 'boolean'],
        ]);

        $normalizedTitle  = trim($validated['title']);
        $normalizedArtist = $validated['artist'] ?? null;

        $existingSong = EventSong::query()
            ->where('event_id', $event->id)
            ->where('title', $normalizedTitle)
            ->where('artist', $normalizedArtist)
            ->where('status', '!=', EventSong::STATUS_REJECTED)
            ->first();

        if ($existingSong) {
            $message = 'Esa canción ya está en la lista, puedes votar por ella.';

            if ($request->expectsJson()) {
                return response()->json([
                    'message'     => $message,
                    'duplicated'  => true,
                    'song'        => ['id' => $existingSong->id],
                ], 422);
            }

            return redirect()
                ->route('events.show', ['slug' => $event->slug, 'i' => $guest->invitation_code])
                ->with('song_error', $message);
        }

        $maxSongsPerGuest = data_get($event->settings, 'playlist_max_songs_per_guest');

        if ($maxSongsPerGuest) {
            $alreadySuggested = $event->songs()
                ->where('suggested_by_guest_id', $guest->id)
                ->count();

            if ($alreadySuggested >= $maxSongsPerGuest) {
                $message = 'Ya ha sugerido el número máximo de canciones permitido para este evento.';

                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => $message,
                    ], 422);
                }

                return redirect()
                    ->route('events.show', ['slug' => $event->slug, 'i' => $guest->invitation_code])
                    ->with('song_error', $message);
            }
        }

        $song = EventSong::create([
            'event_id'              => $event->id,
            'title'                 => $normalizedTitle,
            'artist'                => $normalizedArtist,
            'url'                   => $validated['url'] ?? null,
            'message_for_couple'    => $validated['message_for_couple'] ?? null,
            'suggested_by_guest_id' => $guest->id,
            'show_author'           => $request->boolean('show_author', true),
            'status'                => EventSong::STATUS_APPROVED,
            'votes_count'           => 0,
        ])->load('suggestedBy');

        $successMessage = '¡Gracias! Su canción ha sido agregada a la lista.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $successMessage,
                'song'    => [
                    'id'                 => $song->id,
                    'title'              => $song->title,
                    'artist'             => $song->artist,
                    'url'                => $song->url,
                    'message_for_couple' => $song->message_for_couple,
                    'votes_count'        => $song->votes_count,
                    'show_author'        => $song->show_author,
                    'suggested_by_name'  => $song->show_author && $song->suggestedBy
                        ? $song->suggestedBy->name
                        : null,
                ],
            ]);
        }

        return redirect()
            ->route('events.show', ['slug' => $event->slug, 'i' => $guest->invitation_code])
            ->with('song_success', $successMessage);
    }

    /**
     * Votar o quitar voto a una canción.
     *
     * @OA\Post(
     *     path="/eventos/{slug}/canciones/{song}/votar",
     *     tags={"Eventos Públicos"},
     *     summary="Votar o quitar voto a una canción del evento",
     *     description="Alterna el voto del invitado actual sobre una canción específica del evento. 
     *                  Soporta HTML (redirect) y JSON (AJAX).",
     *     operationId="publicVoteSong",
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="Slug del evento",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="song",
     *         in="path",
     *         required=true,
     *         description="ID de la canción del evento",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"invitation_code"},
     *             @OA\Property(property="invitation_code", type="string", example="DEMO1234")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Voto registrado o retirado correctamente (JSON)."
     *     ),
     *     @OA\Response(
     *         response=302,
     *         description="Redirección a la página del evento con mensaje flash."
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Invitación no encontrada o límite de votos alcanzado."
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Evento o canción no encontrados, o módulo de canciones desactivado."
     *     )
     * )
     */
    public function vote(string $slug, EventSong $song, Request $request)
    {
        $event = Event::publicVisible()
            ->where('slug', $slug)
            ->firstOrFail();

        // Módulo activo
        if (! data_get($event->modules, 'songs')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'El módulo de canciones no está disponible para este evento.',
                ], 404);
            }

            abort(404);
        }

        if ($song->event_id !== $event->id || $song->status !== EventSong::STATUS_APPROVED) {
            $message = 'La canción no pertenece a este evento o no está disponible.';

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                ], 404);
            }

            abort(404);
        }

        $invitationCode = $request->input('invitation_code');

        $guest = Guest::query()
            ->where('event_id', $event->id)
            ->where('invitation_code', $invitationCode)
            ->first();

        if (! $guest) {
            $message = 'No pudimos identificar su invitación. Use el enlace personal que recibió.';

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                ], 422);
            }

            return redirect()
                ->route('events.show', ['slug' => $event->slug, 'i' => $invitationCode])
                ->with('song_vote_error', $message);
        }

        $maxVotesPerGuest = data_get($event->settings, 'playlist_max_votes_per_guest');

        $existingVote = SongVote::query()
            ->where('event_id', $event->id)
            ->where('song_id', $song->id)
            ->where('guest_id', $guest->id)
            ->first();

        $hasVoted = false;
        $message  = '';

        if ($existingVote) {
            $existingVote->delete();

            if ($song->votes_count > 0) {
                $song->decrement('votes_count');
            }

            $hasVoted = false;
            $message  = 'Has retirado tu voto de esta canción.';
        } else {
            if ($maxVotesPerGuest) {
                $currentVotes = SongVote::query()
                    ->where('event_id', $event->id)
                    ->where('guest_id', $guest->id)
                    ->count();

                if ($currentVotes >= $maxVotesPerGuest) {
                    $errorMessage = 'Ya ha usado todos sus votos disponibles para este evento.';

                    if ($request->expectsJson()) {
                        return response()->json([
                            'message' => $errorMessage,
                        ], 422);
                    }

                    return redirect()
                        ->route('events.show', ['slug' => $event->slug, 'i' => $guest->invitation_code])
                        ->with('song_vote_error', $errorMessage);
                }
            }

            SongVote::create([
                'event_id'   => $event->id,
                'song_id'    => $song->id,
                'guest_id'   => $guest->id,
                'fingerprint'=> null,
            ]);

            $song->increment('votes_count');

            $hasVoted = true;
            $message  = 'Tu voto ha sido registrado.';
        }

        $song->refresh();

        if ($request->expectsJson()) {
            return response()->json([
                'message'     => $message,
                'song_id'     => $song->id,
                'votes_count' => $song->votes_count,
                'has_voted'   => $hasVoted,
            ]);
        }

        return redirect()
            ->route('events.show', ['slug' => $event->slug, 'i' => $guest->invitation_code])
            ->with('song_vote_success', $message);
    }
}
