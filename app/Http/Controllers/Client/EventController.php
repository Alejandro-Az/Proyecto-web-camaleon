<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Event;

class EventController extends Controller
{
    public function index()
    {
        $userId = auth('client')->id();

        $events = Event::query()
            ->where('owner_user_id', $userId)
            ->orderByDesc('event_date')
            ->get();

        return view('client.events.index', [
            'events' => $events,
        ]);
    }

    /**
     * Detalle/administración del evento del cliente (por ID).
     *
     * Regla de seguridad:
     * - Si el evento no pertenece al cliente, devolvemos 404 (no revelamos existencia).
     */
    public function show(int $eventId)
    {
        $userId = auth('client')->id();

        $event = Event::query()
            ->whereKey($eventId)
            ->where('owner_user_id', $userId)
            ->firstOrFail();

        return view('client.events.show', [
            'event' => $event,
        ]);
    }
}
