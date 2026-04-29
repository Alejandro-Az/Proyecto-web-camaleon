<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventSchedule;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ScheduleController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request, int $eventId)
    {
        $event = Event::findOrFail($eventId);
        $this->authorize('update', $event);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
            'starts_at' => ['required', 'date_format:Y-m-d H:i:s'], // Expecting full datetime
            'ends_at' => ['nullable', 'date_format:Y-m-d H:i:s', 'after_or_equal:starts_at'],
            'location_label' => ['nullable', 'string', 'max:150'],
            'location_type' => ['nullable', 'string', 'max:50'],
            'display_order' => ['integer', 'min:0'],
            'is_enabled' => ['boolean'],
        ]);

        $validated['is_enabled'] = $request->boolean('is_enabled');
        $validated['display_order'] = $request->input('display_order', 0);

        $event->schedules()->create($validated);

        return redirect()->back()->with('success', 'Actividad agregada al itinerario correctamente.');
    }

    public function update(Request $request, EventSchedule $schedule)
    {
        $schedule->load('event');
        $this->authorize('update', $schedule->event);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
            'starts_at' => ['required', 'date_format:Y-m-d H:i:s'],
            'ends_at' => ['nullable', 'date_format:Y-m-d H:i:s', 'after_or_equal:starts_at'],
            'location_label' => ['nullable', 'string', 'max:150'],
            'location_type' => ['nullable', 'string', 'max:50'],
            'display_order' => ['integer', 'min:0'],
            'is_enabled' => ['boolean'],
        ]);

        $validated['is_enabled'] = $request->boolean('is_enabled');

        $schedule->update($validated);

        return redirect()->back()->with('success', 'Actividad actualizada correctamente.');
    }

    public function destroy(EventSchedule $schedule)
    {
        $schedule->load('event');
        $this->authorize('update', $schedule->event);

        $schedule->delete();

        return redirect()->back()->with('success', 'Actividad eliminada correctamente.');
    }
}
