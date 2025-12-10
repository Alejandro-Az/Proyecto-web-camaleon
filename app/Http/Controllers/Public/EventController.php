<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Event;

class EventController extends Controller
{
    /**
     * Muestra la página pública de un evento a partir de su slug.
     *
     * Ruta: /eventos/{slug}
     */
    public function show(string $slug)
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

        return view('events.show', [
            'event' => $event,
        ]);
    }
}
