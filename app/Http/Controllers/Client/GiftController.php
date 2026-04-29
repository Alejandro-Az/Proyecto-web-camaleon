<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventGift;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class GiftController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request, int $eventId)
    {
        $event = Event::findOrFail($eventId);
        $this->authorize('update', $event);

        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:150'],
            'description'   => ['nullable', 'string', 'max:500'],
            'store_label'   => ['nullable', 'string', 'max:100'],
            'url'           => ['nullable', 'url', 'max:500'],
            'quantity'      => ['required', 'integer', 'min:1', 'max:999'],
            'display_order' => ['integer', 'min:0'],
        ]);

        $validated['display_order'] = $request->input('display_order', 0);
        $validated['quantity_reserved'] = 0;
        $validated['status'] = EventGift::STATUS_PENDING;

        $event->gifts()->create($validated);

        return redirect()->back()->with('success', 'Regalo añadido correctamente.');
    }

    public function update(Request $request, EventGift $gift)
    {
        $gift->load('event');
        $this->authorize('update', $gift->event);

        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:150'],
            'description'   => ['nullable', 'string', 'max:500'],
            'store_label'   => ['nullable', 'string', 'max:100'],
            'url'           => ['nullable', 'url', 'max:500'],
            'quantity'      => ['required', 'integer', 'min:1', 'max:999'],
            'display_order' => ['integer', 'min:0'],
        ]);

        $gift->update($validated);

        return redirect()->back()->with('success', 'Regalo actualizado correctamente.');
    }

    public function destroy(EventGift $gift)
    {
        $gift->load('event');
        $this->authorize('update', $gift->event);

        $gift->delete();

        return redirect()->back()->with('success', 'Regalo eliminado correctamente.');
    }
}
