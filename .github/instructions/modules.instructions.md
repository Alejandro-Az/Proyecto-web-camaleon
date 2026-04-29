---
applyTo: "routes/modules/**,config/event_modules.php,config/event_plans.php"
description: "Conventions for adding or modifying event feature modules in Camaleón"
---

# Module System Conventions

## What is a Module?

A module is a feature toggle that enables/disables a section of a public event page. Examples: `songs`, `gifts`, `story`, `rsvp`, `dress_code`.

## Adding a New Module — Checklist

1. **Register the module key** in `config/event_modules.php` under `defaults`:
   ```php
   'defaults' => [
       'my_module' => false, // false = opt-in, true = opt-out
   ],
   ```

2. **Add it to the plan tiers** in `config/event_plans.php` — decide if `standard` or `premium` only.

3. **Create route file** `routes/modules/my_module_public.php` (and `my_module_admin.php` if needed). Use the pattern:
   ```php
   Route::prefix('eventos/{slug}/my-module')
       ->name('events.my_module.')
       ->group(function () { ... });
   ```
   Then register it in `routes/web.php` with `require __DIR__.'/modules/my_module_public.php';`.

4. **Create controllers** in the correct namespace:
   - `App\Http\Controllers\Public\MyModuleController` — guest-facing
   - `App\Http\Controllers\Client\MyModuleController` — panel CRUD (if needed)

5. **Gate on the module** in every public controller method:
   ```php
   $event = Event::where('slug', $slug)->firstOrFail();
   abort_unless($event->hasModule('my_module'), 404);
   ```

6. **Blade guard** in `resources/views/events/show.blade.php`:
   ```blade
   @if($event->hasModule('my_module'))
       {{-- section HTML --}}
   @endif
   ```

7. **Throttle mutations** — apply `throttle:20,1` to any `POST`/`PATCH`/`DELETE` public route.

## Legacy Aliases

If renaming a module key, add a mapping to `config/event_modules.php` under `aliases`:
```php
'aliases' => [
    'old_key' => 'new_canonical_key',
],
```
`Event::hasModule()` resolves aliases automatically — never check the old key directly.
