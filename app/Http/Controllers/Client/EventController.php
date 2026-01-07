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
}
