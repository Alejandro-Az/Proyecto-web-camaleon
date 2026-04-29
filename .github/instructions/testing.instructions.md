---
applyTo: "tests/Feature/**,tests/Unit/**"
description: "Testing conventions for the Camaleón Laravel project"
---

# Testing Conventions

## Stack

- **PHPUnit 10** via `php artisan test` or `./vendor/bin/phpunit`
- **SQLite in-memory** — no MySQL needed (configured in `phpunit.xml`)
- Always use `use Illuminate\Foundation\Testing\RefreshDatabase;` in every test class

## Feature Test Structure

```php
namespace Tests\Feature;

use App\Models\Event;
use App\Models\Guest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MyFeatureTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function guest_can_do_something(): void
    {
        $event = Event::factory()->create(['slug' => 'my-event', 'status' => Event::STATUS_ACTIVE]);
        // ...
        $this->post("/eventos/{$event->slug}/action", [...])
             ->assertRedirect(...);
    }
}
```

## Auth Patterns

### Client panel (session guard)
```php
$user = \App\Models\User::factory()->create(['role' => 'client']);
$this->actingAs($user, 'client');
```

### API / admin (JWT)
Use `$this->withHeaders(['Authorization' => 'Bearer '.$token])` or mock the `auth:api` middleware.

## Key Factories

| Factory | Notes |
|---------|-------|
| `Event::factory()` | Always set `slug`, `status => Event::STATUS_ACTIVE` for public-facing tests |
| `Guest::factory()` | Requires `event_id`; set `invitation_code` for RSVP tests |
| `EventGift::factory()` | Requires `event_id` |
| `EventLocation::factory()` | Requires `event_id` |

## Public Route Tests

- Public event URL: `GET /eventos/{slug}`
- RSVP: `POST /eventos/{slug}/rsvp`
- Songs: `POST /eventos/{slug}/canciones`
- Gifts: `POST /eventos/{slug}/regalos/{gift}/reservar`
- Guest photos: `POST /eventos/{slug}/fotos-invitados`

## Module Visibility Tests

To test that a section is hidden when a module is off:
```php
$event = Event::factory()->create([
    'slug'    => 'test-event',
    'modules' => json_encode(['songs' => false]),
]);
$this->get("/eventos/{$event->slug}")->assertDontSee('id="canciones"');
```

## Unit Tests

Unit tests in `tests/Unit/` cover config logic (module defaults, plan tiers). Keep them pure — no database, no HTTP.
