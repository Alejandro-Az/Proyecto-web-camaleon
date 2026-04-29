# Camaleón — Agent Instructions

## Project Overview

**Camaleón** is a Laravel 10 wedding/event invitation platform. Event organizers create themeable digital event pages; guests interact via public URLs (RSVP, song voting, gift registry, photo upload).

## Key Commands

```bash
php artisan serve          # Laravel dev server
npm run dev                # Vite HMR (frontend assets)
npm run build              # Production frontend build
php artisan test           # Run all tests (SQLite in-memory)
./vendor/bin/pint          # Code style (Laravel Pint)
php artisan l5-swagger:generate  # Regenerate OpenAPI docs
```

## Architecture

### Controller Namespaces

| Namespace | Location | Auth | Purpose |
|-----------|----------|------|---------|
| `App\Http\Controllers\Api\` | `app/Http/Controllers/Api/` | JWT bearer | Admin JSON API |
| `App\Http\Controllers\Admin\` | `app/Http/Controllers/Admin/` | JWT + `role:admin` | Admin-only actions |
| `App\Http\Controllers\Client\` | `app/Http/Controllers/Client/` | Session (`auth:client`) | Event owner panel |
| `App\Http\Controllers\Public\` | `app/Http/Controllers/Public/` | None | Guest-facing public routes |

### Route Structure

| Group | Prefix | File |
|-------|--------|------|
| API auth | `POST /api/auth/…` | `routes/api.php` |
| API admin | `POST /api/admin/…` | `routes/api.php` |
| Public event pages | `GET /eventos/{slug}` | `routes/web.php` |
| Client panel | `/panel/…` | `routes/panel.php` |

Module-specific routes live in `routes/modules/`.

### Core Model: `Event`

`Event` is the central model. All others (`EventLocation`, `EventSchedule`, `EventGift`, `EventGiftClaim`, `EventSong`, `SongVote`, `EventPhoto`, `EventDressCode`, `EventRomanticPhrase`, `EventStory`, `Guest`) relate through `event_id`.

Key `events` columns: `slug` (URL key), `plan_key`, `modules` (JSON), `settings` (JSON), `owner_user_id`.

### Authentication

- **Web panel (clients):** Session guard named `client` + `client.role` middleware. Login: `POST /panel/login`.
- **API (admins):** JWT via `tymon/jwt-auth`. Login: `POST /api/auth/login` → Bearer token. Use `auth:api` + `role:admin` middleware on protected routes.
- **Sanctum** is installed but not active.

## Module & Plan System

- `config/event_modules.php` — default module flags and legacy aliases. `Event::hasModule($key)` checks if a module is enabled.
- `config/event_plans.php` — `standard` and `premium` tiers defining which modules are available. Events store `plan_key`.
- Module flags control which sections render on public event pages (songs, gifts, guest photos, story, etc.).

## Testing Conventions

- Tests use **SQLite in-memory** — no MySQL required (`phpunit.xml`).
- Feature tests cover full HTTP flows (auth, RSVP, gifts, photos, panel CRUD).
- Use `php artisan test` or `./vendor/bin/phpunit`.
- Test files: `tests/Feature/` and `tests/Unit/`.

## API Documentation (Swagger)

- Global OpenAPI info and `bearerAuth` security scheme declared in `app/Swagger/OpenApi.php`.
- Annotate controllers with `@OA\*` docblocks.
- Generated spec in `storage/api-docs/`. Config: `config/l5-swagger.php`.
- Regenerate with `php artisan l5-swagger:generate`.

## Frontend

- **Tailwind CSS v4** with PostCSS. Config: `tailwind.config.js`.
- **Vite** with `laravel-vite-plugin`. Entry points in `resources/js/` and `resources/css/app.css`.
- **Blade** templates in `resources/views/` — `client/` for panel, `events/` for public pages, `layouts/` for base layouts.

## Conventions

- Soft deletes are used on `events` and `guests`.
- `display_order` column controls sort order for schedules, gifts, stories.
- Throttle rate limiting applied to public gift endpoints (`throttle:20,1` for mutations).
- Slugs are the public URL identifier for events — always unique.
- Database seeders: `DemoUsersSeeder`, `DemoEventsSeeder` for local development data.
