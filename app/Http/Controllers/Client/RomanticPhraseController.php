<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventRomanticPhrase;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class RomanticPhraseController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request, int $eventId)
    {
        $event = Event::findOrFail($eventId);
        $this->authorize('update', $event);

        $validated = $request->validate([
            'phrase' => ['required', 'string', 'max:500'],
            'author' => ['nullable', 'string', 'max:150'],
            'display_order' => ['integer', 'min:0'],
            'is_enabled' => ['boolean'],
        ]);

        $validated['is_enabled'] = $request->boolean('is_enabled');
        $validated['display_order'] = $request->input('display_order', 0);

        $event->romanticPhrases()->create($validated);

        return redirect()->back()->with('success', 'Frase agregada correctamente.');
    }

    public function update(Request $request, EventRomanticPhrase $phrase)
    {
        // Parameter name 'phrase' comes from route model binding 'phrases' -> 'phrase' (singular)
        // Check route definition if it uses 'phrase' or 'romantic_phrase'
        // Convention for resource 'eventos.phrases' -> parameter {phrase}
        
        $phrase->load('event');
        $this->authorize('update', $phrase->event);

        $validated = $request->validate([
            'phrase' => ['required', 'string', 'max:500'],
            'author' => ['nullable', 'string', 'max:150'],
            'display_order' => ['integer', 'min:0'],
            'is_enabled' => ['boolean'],
        ]);

        $validated['is_enabled'] = $request->boolean('is_enabled');

        $phrase->update($validated);

        return redirect()->back()->with('success', 'Frase actualizada correctamente.');
    }

    public function destroy(EventRomanticPhrase $phrase)
    {
        $phrase->load('event');
        $this->authorize('update', $phrase->event);

        $phrase->delete();

        return redirect()->back()->with('success', 'Frase eliminada correctamente.');
    }
}
