<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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

    public function create()
    {
        return view('client.events.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'type'       => ['required', 'string', 'in:wedding,xv,birthday,baby_shower,other'],
            'event_date' => ['required', 'date', 'after:today'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'plan_key'   => ['required', 'string', 'in:standard,premium'],
        ]);

        $baseSlug = Str::slug($validated['name']);
        $slug = $baseSlug;
        $i = 1;
        while (Event::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $i++;
        }

        $event = Event::create([
            'name'           => $validated['name'],
            'type'           => $validated['type'],
            'event_date'     => $validated['event_date'],
            'start_time'     => $validated['start_time'] ?? '18:00:00',
            'slug'           => $slug,
            'plan_key'       => $validated['plan_key'],
            'status'         => Event::STATUS_ACTIVE,
            'owner_user_id'  => auth('client')->id(),
            'theme_key'      => 'default',
            'primary_color'  => '#f472b6',
            'secondary_color'=> '#0f172a',
            'accent_color'   => '#f9a8d4',
            'font_family'    => 'Playfair Display',
            'modules'        => Event::normalizeModulesForStorage([], $validated['plan_key']),
            'settings'       => [],
        ]);

        return redirect()->route('client.events.show', $event->id)
            ->with('success', 'Evento creado correctamente. ¡Ya puedes personalizarlo!');
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

    public function update(\App\Http\Requests\Client\UpdateEventRequest $request, int $eventId)
    {
        $userId = auth('client')->id();
        $event = Event::query()
            ->whereKey($eventId)
            ->where('owner_user_id', $userId)
            ->firstOrFail();

        // 1. Basic info
        $event->fill($request->only([
            'name', 'event_date', 'end_time'
        ]));

        // Normalize start_time to H:i:s (SQLite stores literally; DB expects full time string)
        if ($request->has('start_time') && $request->input('start_time')) {
            $raw = $request->input('start_time');
            $event->start_time = strlen($raw) === 5 ? $raw . ':00' : $raw;
        }

        // 2. Modules (merge logic)
        if ($request->has('modules')) {
            $currentModules = $event->modules ?? [];
            if (!is_array($currentModules)) {
                $currentModules = [];
            }
            
            // Merge request modules into current
            foreach ($request->input('modules', []) as $key => $value) {
                // Check if allowed by plan before enabling
                if ($value == true && !$event->isModuleAvailable($key)) {
                    continue; // Skip enabling forbidden modules
                }
                $currentModules[$key] = (bool) $value;
            }
            
            $event->modules = $currentModules;
        }

        // 3. Settings (merge logic)
        if ($request->has('settings')) {
            $currentSettings = $event->settings ?? [];
            if (!is_array($currentSettings)) {
                $currentSettings = [];
            }

            foreach ($request->input('settings', []) as $key => $value) {
                $currentSettings[$key] = $value;
            }
            $event->settings = $currentSettings;
        }

        $event->save();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Evento actualizado correctamente',
                'event' => $event,
            ]);
        }

        return back()->with('success', 'Evento actualizado correctamente');
    }

    public function updateHero(\Illuminate\Http\Request $request, int $eventId)
    {
        $request->validate([
            'hero_image' => ['required', 'image', 'max:5120'], // 5MB max
        ]);

        $userId = auth('client')->id();
        $event = Event::query()
            ->whereKey($eventId)
            ->where('owner_user_id', $userId)
            ->firstOrFail();

        if ($request->hasFile('hero_image')) {
            // 1. Delete old image if exists
            if ($event->cover_image_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($event->cover_image_path)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($event->cover_image_path);
            }

            // 2. Store new image
            $file = $request->file('hero_image');
            $path = $file->storePublicly("events/{$event->id}/hero", 'public');
            
            $event->cover_image_path = $path;
            $event->save();
        }

        return response()->json([
            'message' => 'Imagen actualizada',
            'path' => $event->cover_image_path,
        ]);
    }
}
