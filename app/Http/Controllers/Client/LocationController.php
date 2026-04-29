<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventLocation;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class LocationController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request, int $eventId)
    {
        $event = Event::findOrFail($eventId);
        $this->authorize('update', $event);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'address' => ['nullable', 'string', 'max:255'],
            'maps_url' => ['nullable', 'url', 'max:500'],
            'type' => ['nullable', 'string', 'max:50'], // ceremony, reception, etc.
            'display_order' => ['integer', 'min:0'],
            'is_enabled' => ['boolean'],
        ]);

        $validated['is_enabled'] = $request->boolean('is_enabled');
        $validated['display_order'] = $request->input('display_order', 0);

        $event->locations()->create($validated);

        return redirect()->back()->with('success', 'Ubicación agregada correctamente.');
    }

    public function update(Request $request, EventLocation $location)
    {
        $location->load('event');
        $this->authorize('update', $location->event);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'address' => ['nullable', 'string', 'max:255'],
            'maps_url' => ['nullable', 'url', 'max:500'],
            'type' => ['nullable', 'string', 'max:50'],
            'display_order' => ['integer', 'min:0'],
            'is_enabled' => ['boolean'],
        ]);

        $validated['is_enabled'] = $request->boolean('is_enabled');

        $location->update($validated);

        return redirect()->back()->with('success', 'Ubicación actualizada correctamente.');
    }

    public function destroy(EventLocation $location)
    {
        $location->load('event');
        $this->authorize('update', $location->event);

        $location->delete();

        return redirect()->back()->with('success', 'Ubicación eliminada correctamente.');
    }
}
