<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Guest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class GuestController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, int $eventId)
    {
        $event = Event::findOrFail($eventId);
        $this->authorize('view', $event);

        $query = $event->guests();

        if ($request->has('status') && in_array($request->status, ['pending', 'yes', 'no', 'maybe'])) {
            $query->where('rsvp_status', $request->status);
        }

        $guests = $query->latest()->paginate(25);

        // Stats for the view
        $stats = [
            'total' => $event->guests()->count(),
            'confirmed_guests' => $event->guests()->where('rsvp_status', 'yes')->sum('guests_confirmed'),
            'yes' => $event->guests()->where('rsvp_status', 'yes')->count(),
            'no' => $event->guests()->where('rsvp_status', 'no')->count(),
            'pending' => $event->guests()->where('rsvp_status', 'pending')->count(),
        ];

        return view('client.guests.index', compact('event', 'guests', 'stats'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, int $eventId)
    {
        $event = Event::findOrFail($eventId);
        $this->authorize('update', $event);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'invited_seats' => ['required', 'integer', 'min:1', 'max:50'],
            'invitation_code' => [
                'nullable', 
                'string', 
                'max:50',
                Rule::unique('guests')->where('event_id', $event->id)
            ],
        ]);

        // Auto-generate unique code if missing
        if (empty($validated['invitation_code'])) {
            do {
                $code = Str::upper(Str::random(8));
            } while ($event->guests()->where('invitation_code', $code)->exists());
            $validated['invitation_code'] = $code;
        }

        $event->guests()->create($validated);

        return redirect()->route('client.events.guests.index', $event->id)
            ->with('success', 'Invitado agregado correctamente.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Guest $guest)
    {
        $guest->load('event');
        $this->authorize('update', $guest->event);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'invited_seats' => ['required', 'integer', 'min:1', 'max:50'],
            'invitation_code' => [
                'required', 
                'string', 
                'max:50',
                Rule::unique('guests')->where('event_id', $guest->event_id)->ignore($guest->id)
            ],
            'rsvp_status' => ['required', 'in:pending,yes,no,maybe'],
            'guests_confirmed' => [
                'nullable', 
                'integer', 
                'min:0', 
                'lte:invited_seats'
            ],
            'show_in_public_list' => ['boolean'],
            'rsvp_message' => ['nullable', 'string', 'max:1000'],
            'dietary_notes' => ['nullable', 'string', 'max:1000'],
            'dietary_tags' => ['nullable', 'array']
        ]);

        // Enforce business rules
        if ($validated['rsvp_status'] === 'no') {
            $validated['guests_confirmed'] = 0;
        } elseif ($validated['rsvp_status'] === 'pending') {
            $validated['guests_confirmed'] = null; // or 0 depending on preference, kept null to signify "unknown"
        } else {
             // yes/maybe
             // If not provided, default to 0 or invited_seats? 
             // Usually for manual update we want explicit value.
             // Validation lte:invited_seats handles max.
        }
        
        // Handle checkbox
        $validated['show_in_public_list'] = $request->boolean('show_in_public_list');

        // Handle arrays and nullable fields
        $validated['dietary_tags'] = $request->input('dietary_tags', []);
        
        if (!$request->has('rsvp_message')) {
             $validated['rsvp_message'] = null; // Ensure we can clear it if needed
        }
        


        $guest->update($validated);

        return redirect()->back()
            ->with('success', 'Invitado actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Guest $guest)
    {
        $guest->load('event');
        $this->authorize('update', $guest->event); // Or specific permission

        $guest->delete();

        return redirect()->back()
            ->with('success', 'Invitado eliminado correctamente.');
    }
}
