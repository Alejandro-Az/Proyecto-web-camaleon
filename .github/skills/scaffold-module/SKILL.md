---
name: scaffold-module
description: "Use when: creating a new event module in Camaleón. Scaffolds migration, model, controllers (Public + Client), route file, Blade section stub, and test."
---

# Skill: Scaffold New Event Module

## Purpose

Automate creation of all files needed to add a new feature module to a Camaleón event page.

## Steps

### 1. Gather Requirements

Ask the user for:
- **Module key** (snake_case, e.g. `photo_booth`) — used in `modules` JSON and config
- **URL segment** (kebab-case, e.g. `cabina-fotos`) — used in route prefix
- **Plan tier** — `standard`, `premium`, or both
- **Default enabled?** — `true` or `false`
- **Needs a DB table?** — if yes, ask for columns

### 2. Register Module

Update `config/event_modules.php` defaults and `config/event_plans.php` tiers.

### 3. Create Migration (if DB needed)

```
php artisan make:migration create_event_{module_key}s_table
```

Add columns: `id`, `event_id` (FK → events), feature-specific columns, `display_order` (if ordered), timestamps.

### 4. Create Model (if DB needed)

```
php artisan make:model Event{ModuleName}
```

- Add `protected $fillable`
- Add `belongsTo Event` relationship
- If ordered: add `protected $casts` for display_order

### 5. Create Public Controller

File: `app/Http/Controllers/Public/{ModuleName}Controller.php`

```php
namespace App\Http\Controllers\Public;

use App\Models\Event;
use Illuminate\Http\Request;

class {ModuleName}Controller extends Controller
{
    public function index(string $slug)
    {
        $event = Event::where('slug', $slug)->firstOrFail();
        abort_unless($event->hasModule('{module_key}'), 404);
        // ...
    }
}
```

### 6. Create Client Controller (if panel CRUD needed)

File: `app/Http/Controllers/Client/{ModuleName}Controller.php`

Follow the existing pattern in `app/Http/Controllers/Client/` — use `auth:client` guard, gate on `$event->owner_user_id === auth('client')->id()`.

### 7. Create Route File

File: `routes/modules/{module_key}_public.php`

```php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Public\{ModuleName}Controller;

Route::prefix('eventos/{slug}/{url-segment}')
    ->name('events.{module_key}.')
    ->group(function () {
        Route::get('/', [{ModuleName}Controller::class, 'index'])->name('index');
        // POST mutations: ->middleware('throttle:20,1')
    });
```

Register in `routes/web.php`:
```php
require __DIR__.'/modules/{module_key}_public.php';
```

### 8. Add Blade Section

In `resources/views/events/show.blade.php`, add:
```blade
@if($event->hasModule('{module_key}'))
    <section id="{url-segment}">
        {{-- Module content --}}
    </section>
@endif
```

### 9. Create Feature Test

File: `tests/Feature/{ModuleName}Test.php`

```php
namespace Tests\Feature;

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class {ModuleName}Test extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function module_is_hidden_when_disabled(): void
    {
        $event = Event::factory()->create([
            'slug'    => 'test',
            'modules' => json_encode(['{module_key}' => false]),
        ]);
        $this->get("/eventos/{$event->slug}/{url-segment}")->assertNotFound();
    }
}
```

### 10. Run Tests

```bash
php artisan test --filter={ModuleName}Test
```
