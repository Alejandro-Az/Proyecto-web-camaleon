<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ContentController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request, int $eventId)
    {
        $event = Event::findOrFail($eventId);
        $this->authorize('update', $event);

        // Load all content relationships
        $event->load([
            'dressCodes'     => fn($q) => $q->orderBy('display_order'),
            'romanticPhrases'=> fn($q) => $q->orderBy('display_order'),
            'stories'        => fn($q) => $q->orderBy('display_order'),
            'schedules'      => fn($q) => $q->orderBy('starts_at')->orderBy('display_order'),
            'locations'      => fn($q) => $q->orderBy('display_order'),
            'gifts'          => fn($q) => $q->orderBy('display_order'),
        ]);

        return view('client.events.content.index', compact('event'));
    }
}
