<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventStory;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class StoryController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request, int $eventId)
    {
        $event = Event::findOrFail($eventId);
        $this->authorize('update', $event);

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:150'],
            'body' => ['required', 'string', 'max:5000'],
            'display_order' => ['integer', 'min:0'],
            'is_enabled' => ['boolean'],
        ]);

        $validated['is_enabled'] = $request->boolean('is_enabled');
        $validated['display_order'] = $request->input('display_order', 0);

        $event->stories()->create($validated);

        return redirect()->back()->with('success', 'Historia agregada correctamente.');
    }

    public function update(Request $request, EventStory $story)
    {
        $story->load('event');
        $this->authorize('update', $story->event);

        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:150'],
            'body' => ['required', 'string', 'max:5000'],
            'display_order' => ['integer', 'min:0'],
            'is_enabled' => ['boolean'],
        ]);

        $validated['is_enabled'] = $request->boolean('is_enabled');

        $story->update($validated);

        return redirect()->back()->with('success', 'Historia actualizada correctamente.');
    }

    public function destroy(EventStory $story)
    {
        $story->load('event');
        $this->authorize('update', $story->event);

        $story->delete();

        return redirect()->back()->with('success', 'Historia eliminada correctamente.');
    }
}
