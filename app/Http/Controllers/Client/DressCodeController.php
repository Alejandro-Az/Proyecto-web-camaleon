<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventDressCode;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class DressCodeController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request, int $eventId)
    {
        $event = Event::findOrFail($eventId);
        $this->authorize('update', $event);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
            'examples' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'display_order' => ['integer', 'min:0'],
            'is_enabled' => ['boolean'],
        ]);

        $validated['is_enabled'] = $request->boolean('is_enabled');
        $validated['display_order'] = $request->input('display_order', 0);

        $event->dressCodes()->create($validated);

        return redirect()->back()->with('success', 'Código de vestimenta agregado correctamente.');
    }

    public function update(Request $request, EventDressCode $dressCode)
    {
        $dressCode->load('event');
        $this->authorize('update', $dressCode->event);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
            'examples' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'display_order' => ['integer', 'min:0'],
            'is_enabled' => ['boolean'],
        ]);

        $validated['is_enabled'] = $request->boolean('is_enabled');

        $dressCode->update($validated);

        return redirect()->back()->with('success', 'Código de vestimenta actualizado correctamente.');
    }

    public function destroy(EventDressCode $dressCode)
    {
        $dressCode->load('event');
        $this->authorize('update', $dressCode->event);

        $dressCode->delete();

        return redirect()->back()->with('success', 'Código de vestimenta eliminado correctamente.');
    }
}
