<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventGift;
use App\Models\EventGiftClaim;
use App\Models\EventLocation;
use App\Models\EventPhoto;
use App\Models\EventSchedule;
use App\Models\EventSong;
use App\Models\EventStory;
use App\Models\Guest;
use App\Models\SongVote;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

// Si estos modelos existen en tu rama, déjalos.
// Si aún no existen, comenta estos 2 use y también las llamadas seedDressCodes/seedRomanticPhrases.
use App\Models\EventDressCode;
use App\Models\EventRomanticPhrase;

class DemoEventsSeeder extends Seeder
{
    private ?int $demoClientId = null;

    public function run(): void
    {
        // Aseguramos disk public (aunque en tests normalmente se usa fake)
        // En dev sirve para que existan archivos reales (svg).
        Storage::disk('public');

        $wedding = $this->seedWedding();
        $xv      = $this->seedXv();

        if ($this->command) {
            $this->command->info('✅ DemoEventsSeeder listo.');
            $this->command->info('URLs rápidas para probar:');
            $this->command->info(' - /eventos/' . $wedding->slug . '?i=DEMO1234');
            $this->command->info(' - /eventos/' . $xv->slug . '?i=XVDEMO1234');
        }
    }

    private function resolveDemoClientId(): ?int
    {
        if ($this->demoClientId !== null) {
            return $this->demoClientId;
        }

        if (!Schema::hasTable('users')) {
            return null;
        }

        $this->demoClientId = User::query()
            ->where('email', 'client.demo@camaleon.test')
            ->value('id');

        return $this->demoClientId;
    }

    /**
     * Borra hijos de un evento para que el seeder sea idempotente.
     * OJO: orden pensado por posibles FKs.
     */
    private function purgeEventChildren(Event $event): void
    {
        if (!Schema::hasTable('events')) {
            return;
        }

        // Claims -> Gifts
        if (Schema::hasTable('event_gift_claims')) {
            EventGiftClaim::query()->where('event_id', $event->id)->delete();
        }
        if (Schema::hasTable('event_gifts')) {
            EventGift::query()->where('event_id', $event->id)->delete();
        }

        // Votes -> Songs
        if (Schema::hasTable('song_votes')) {
            SongVote::query()->where('event_id', $event->id)->delete();
        }
        if (Schema::hasTable('event_songs')) {
            EventSong::query()->where('event_id', $event->id)->delete();
        }

        // Story/DressCode apuntan a fotos (example_photo_id) => borrar ANTES de fotos
        if (Schema::hasTable('event_stories')) {
            EventStory::query()->where('event_id', $event->id)->delete();
        }
        if (class_exists(EventDressCode::class) && Schema::hasTable('event_dress_codes')) {
            EventDressCode::query()->where('event_id', $event->id)->delete();
        }

        // Phrases, schedule, locations, photos
        if (class_exists(EventRomanticPhrase::class) && Schema::hasTable('event_romantic_phrases')) {
            EventRomanticPhrase::query()->where('event_id', $event->id)->delete();
        }
        if (Schema::hasTable('event_schedules')) {
            EventSchedule::query()->where('event_id', $event->id)->delete();
        }
        if (Schema::hasTable('event_locations')) {
            EventLocation::query()->where('event_id', $event->id)->delete();
        }
        if (Schema::hasTable('event_photos')) {
            EventPhoto::query()->where('event_id', $event->id)->delete();
        }

        // Guests al final
        if (Schema::hasTable('guests')) {
            Guest::query()->where('event_id', $event->id)->delete();
        }
    }

    private function seedWedding(): Event
    {
        $eventDate = Carbon::create(2025, 12, 31);

        $premiumKey = \defined(Event::class . '::PLAN_PREMIUM') ? Event::PLAN_PREMIUM : 'premium';

        $attrs = [
            'type'       => 'wedding',
            'name'       => 'Boda de Prueba Ana & Luis',
            'slug'       => 'boda-prueba-ana-luis',
            'status'     => \defined(Event::class . '::STATUS_ACTIVE') ? Event::STATUS_ACTIVE : 'active',
            'event_date' => $eventDate,
            'start_time' => '18:00:00',
            'end_time'   => '02:00:00',

            'theme_key'       => 'romantic',
            'primary_color'   => '#f472b6',
            'secondary_color' => '#0f172a',
            'accent_color'    => '#f9a8d4',
            'font_family'     => 'Playfair Display',

            // ✅ Guarda overrides (NO normalices aquí; el modelo se encarga)
            'modules' => [
                'public_attendance_list' => false,
                'guest_photos_upload'    => true,
                'romantic_phrases'       => true,
                'dress_code'             => true,
                'countdown'              => true,
                'map'                    => true,
                'schedule'               => true,
                'gallery'                => true,
                'songs'                  => true,
                'rsvp'                   => true,
                'gifts'                  => true,
                'story'                  => true,
            ],

            'settings' => [
                'playlist_enabled'                   => true,
                'playlist_allow_guests_to_add_songs' => true,
                'playlist_max_songs_per_guest'       => 3,
                'playlist_max_votes_per_guest'       => 10,
                'public_show_song_author'            => true,

                'gifts_require_invitation_code'      => true,
                'gifts_allow_unclaim'                => true,
                'gifts_hide_purchased_from_public'   => false,
                'gifts_allow_multi_unit_reserve'     => true,
                'gifts_show_claimers_public'         => true,
                'gifts_max_units_per_guest_per_gift' => 2,

                'guest_photos_max_per_guest'         => 5,
                'guest_photos_auto_approve'          => false,

                'story_intro'                        => 'Un poquito sobre este momento especial.',
            ],

            'owner_name'  => 'Ana & Luis',
            'owner_email' => 'ana.y.luis@example.com',

            'auto_cleanup_after_days' => 60,
        ];

        // ✅ Forzar plan premium si existe columna
        if (Schema::hasTable('events') && Schema::hasColumn('events', 'plan_key')) {
            $attrs['plan_key'] = $premiumKey;
        }

        // ✅ Ownership si existe columna
        if (Schema::hasTable('events') && Schema::hasColumn('events', 'owner_user_id')) {
            $attrs['owner_user_id'] = $this->resolveDemoClientId();
        }

        $wedding = Event::query()->updateOrCreate(
            ['slug' => $attrs['slug']],
            $attrs
        );

        // ✅ Limpia todo lo hijo para evitar duplicados
        $this->purgeEventChildren($wedding);

        // Locations
        $this->seedLocations($wedding, [
            [
                'type'          => 'ceremony',
                'name'          => 'Iglesia de Prueba',
                'address'       => 'Centro, Ciudad de Ejemplo',
                'maps_url'      => 'https://maps.google.com',
                'display_order' => 1,
            ],
            [
                'type'          => 'reception',
                'name'          => 'Salón Jardín de Ejemplo',
                'address'       => 'Av. Principal 123, Ciudad de Ejemplo',
                'maps_url'      => 'https://maps.google.com',
                'display_order' => 2,
            ],
        ]);

        // Guests
        $guests = $this->seedGuests($wedding, [
            [
                'name'                => 'Invitado Demo',
                'email'               => 'invitado.demo@example.com',
                'invitation_code'     => 'DEMO1234',
                'invited_seats'       => 2,
                'rsvp_status'         => Guest::RSVP_PENDING ?? 'pending',
                'rsvp_public'         => false,
                'guests_confirmed'    => null,
                'show_in_public_list' => false,
            ],
            [
                'name'                => 'María Confirmada',
                'email'               => 'maria.confirmada@example.com',
                'invitation_code'     => 'MARIAYES1',
                'invited_seats'       => 2,
                'rsvp_status'         => Guest::RSVP_YES ?? 'yes',
                'rsvp_public'         => true,
                'guests_confirmed'    => 2,
                'show_in_public_list' => true,
            ],
            [
                'name'                => 'Carlos No Asiste',
                'email'               => 'carlos.no@example.com',
                'invitation_code'     => 'CARLOSNO1',
                'invited_seats'       => 1,
                'rsvp_status'         => Guest::RSVP_NO ?? 'no',
                'rsvp_public'         => false,
                'guests_confirmed'    => 0,
                'show_in_public_list' => false,
            ],
        ]);

        // Schedule
        $this->seedSchedules($wedding, $eventDate, [
            [
                'title'          => 'Ceremonia',
                'description'    => 'Inicio de la ceremonia.',
                'starts_at'      => $eventDate->copy()->setTime(17, 0),
                'ends_at'        => $eventDate->copy()->setTime(18, 0),
                'location_label' => 'Iglesia de Prueba',
                'location_type'  => 'ceremony',
                'display_order'  => 1,
            ],
            [
                'title'          => 'Recepción / Cóctel',
                'description'    => 'Llegada y bienvenida.',
                'starts_at'      => $eventDate->copy()->setTime(18, 30),
                'ends_at'        => $eventDate->copy()->setTime(19, 30),
                'location_label' => 'Salón Jardín de Ejemplo',
                'location_type'  => 'reception',
                'display_order'  => 2,
            ],
            [
                'title'          => 'Cena',
                'description'    => 'Servicio de cena.',
                'starts_at'      => $eventDate->copy()->setTime(20, 0),
                'ends_at'        => $eventDate->copy()->setTime(21, 0),
                'location_label' => 'Salón Jardín de Ejemplo',
                'location_type'  => 'reception',
                'display_order'  => 3,
            ],
            [
                'title'          => 'Baile',
                'description'    => '¡A bailar!',
                'starts_at'      => $eventDate->copy()->setTime(21, 30),
                'ends_at'        => $eventDate->copy()->addDay()->setTime(1, 30),
                'location_label' => 'Salón Jardín de Ejemplo',
                'location_type'  => 'reception',
                'display_order'  => 4,
            ],
        ]);

        // Songs
        $songs = $this->seedSongs($wedding, [
            [
                'title'              => 'Perfect',
                'artist'             => 'Ed Sheeran',
                'url'                => 'https://open.spotify.com/track/0tgVpDi06FyKpA1z0VMD4v',
                'message_for_couple' => 'Para el primer baile, obvio 💕',
                'suggested_by_guest' => 'DEMO1234',
                'show_author'        => true,
                'status'             => \defined(EventSong::class . '::STATUS_APPROVED') ? EventSong::STATUS_APPROVED : 'approved',
            ],
            [
                'title'              => 'Can\'t Help Falling in Love',
                'artist'             => 'Elvis Presley',
                'url'                => null,
                'message_for_couple' => 'Para el vals con los papás.',
                'suggested_by_guest' => 'MARIAYES1',
                'show_author'        => true,
                'status'             => \defined(EventSong::class . '::STATUS_APPROVED') ? EventSong::STATUS_APPROVED : 'approved',
            ],
            [
                'title'              => 'Marry You',
                'artist'             => 'Bruno Mars',
                'url'                => null,
                'message_for_couple' => 'Para levantar a todos de las mesas.',
                'suggested_by_guest' => 'DEMO1234',
                'show_author'        => true,
                'status'             => \defined(EventSong::class . '::STATUS_APPROVED') ? EventSong::STATUS_APPROVED : 'approved',
            ],
            [
                'title'              => 'A Thousand Years',
                'artist'             => 'Christina Perri',
                'url'                => null,
                'message_for_couple' => 'Clásico romántico.',
                'suggested_by_guest' => 'DEMO1234',
                'show_author'        => false,
                'status'             => \defined(EventSong::class . '::STATUS_PENDING') ? EventSong::STATUS_PENDING : 'pending',
            ],
        ], $guests);

        // Votes (para "Perfect")
        if (isset($songs['Perfect'])) {
            $this->seedSongVotes($wedding, $songs['Perfect'], [
                ['guest_code' => 'DEMO1234', 'qty' => 1],
                ['guest_code' => 'MARIAYES1', 'qty' => 1],
            ], $guests);
        }

        // Photos (crea archivos dummy en storage para ver algo en navegador)
        $this->seedPhotos($wedding, [
            [
                'type'           => \defined(EventPhoto::class . '::TYPE_HERO') ? EventPhoto::TYPE_HERO : 'hero',
                'file_path'      => "events/{$wedding->id}/photos/originals/hero-boda.svg",
                'thumbnail_path' => null,
                'caption'        => 'Portada del evento',
                'status'         => \defined(EventPhoto::class . '::STATUS_APPROVED') ? EventPhoto::STATUS_APPROVED : 'approved',
                'display_order'  => 1,
                'guest_code'     => null,
                'svg_title'      => 'Hero Boda',
                'svg_subtitle'   => 'Portada demo',
            ],
            [
                'type'           => \defined(EventPhoto::class . '::TYPE_GALLERY') ? EventPhoto::TYPE_GALLERY : 'gallery',
                'file_path'      => "events/{$wedding->id}/photos/originals/boda-1.svg",
                'thumbnail_path' => null,
                'caption'        => 'Sesión de fotos de compromiso',
                'status'         => \defined(EventPhoto::class . '::STATUS_APPROVED') ? EventPhoto::STATUS_APPROVED : 'approved',
                'display_order'  => 1,
                'guest_code'     => null,
                'svg_title'      => 'Boda 1',
                'svg_subtitle'   => 'Galería demo',
            ],
            [
                'type'           => \defined(EventPhoto::class . '::TYPE_GUEST_UPLOAD') ? EventPhoto::TYPE_GUEST_UPLOAD : 'guest_upload',
                'file_path'      => "events/{$wedding->id}/guest-photos/originals/guest-1.svg",
                'thumbnail_path' => null,
                'caption'        => 'Selfie en la entrada 😄',
                'status'         => \defined(EventPhoto::class . '::STATUS_APPROVED') ? EventPhoto::STATUS_APPROVED : 'approved',
                'display_order'  => 1,
                'guest_code'     => 'DEMO1234',
                'svg_title'      => 'Guest 1',
                'svg_subtitle'   => 'Subida invitado',
            ],
        ], $guests);

        // Gifts
        $gifts = $this->seedGifts($wedding, [
            [
                'name'          => 'Juego de vasos de cristal',
                'description'   => 'Set de 6 vasos de cristal para bebidas.',
                'store_label'   => 'Liverpool',
                'url'           => 'https://example.com/regalo-vasos',
                'quantity'      => 1,
                'display_order' => 1,
            ],
            [
                'name'          => 'Juego de platos x2',
                'description'   => 'Juegos de platos llanos y hondos para 4 personas.',
                'store_label'   => 'Amazon',
                'url'           => 'https://example.com/regalo-platos',
                'quantity'      => 2,
                'display_order' => 2,
            ],
            [
                'name'          => 'Tostador eléctrico',
                'description'   => 'Tostador para 4 rebanadas.',
                'store_label'   => 'Coppel',
                'url'           => 'https://example.com/regalo-tostador',
                'quantity'      => 1,
                'display_order' => 3,
            ],
            [
                'name'          => 'Juego de copas (multi-unidad)',
                'description'   => 'Copas para brindis (se puede apartar más de 1).',
                'store_label'   => 'Amazon',
                'url'           => 'https://example.com/regalo-copas',
                'quantity'      => 5,
                'display_order' => 4,
            ],
        ]);

        if (isset($gifts['Juego de platos x2'], $guests['MARIAYES1'])) {
            $this->seedGiftClaim($wedding, $gifts['Juego de platos x2'], $guests['MARIAYES1'], 1, \defined(EventGiftClaim::class.'::STATUS_RESERVED') ? EventGiftClaim::STATUS_RESERVED : 'reserved');
            $this->syncGiftFromClaims($gifts['Juego de platos x2']);
        }
        if (isset($gifts['Tostador eléctrico'], $guests['DEMO1234'])) {
            $this->seedGiftClaim($wedding, $gifts['Tostador eléctrico'], $guests['DEMO1234'], 1, \defined(EventGiftClaim::class.'::STATUS_PURCHASED') ? EventGiftClaim::STATUS_PURCHASED : 'purchased');
            $this->syncGiftFromClaims($gifts['Tostador eléctrico']);
        }
        if (isset($gifts['Juego de copas (multi-unidad)'], $guests['DEMO1234'], $guests['MARIAYES1'])) {
            $this->seedGiftClaim($wedding, $gifts['Juego de copas (multi-unidad)'], $guests['DEMO1234'], 2, \defined(EventGiftClaim::class.'::STATUS_RESERVED') ? EventGiftClaim::STATUS_RESERVED : 'reserved');
            $this->seedGiftClaim($wedding, $gifts['Juego de copas (multi-unidad)'], $guests['MARIAYES1'], 1, \defined(EventGiftClaim::class.'::STATUS_RESERVED') ? EventGiftClaim::STATUS_RESERVED : 'reserved');
            $this->syncGiftFromClaims($gifts['Juego de copas (multi-unidad)']);
        }

        // Miscelánea
        $this->seedDressCodes($wedding);
        $this->seedRomanticPhrases($wedding);

        // Story
        $this->seedStories($wedding);

        return $wedding;
    }

    private function seedXv(): Event
    {
        $eventDate = Carbon::create(2026, 5, 15);

        $premiumKey = \defined(Event::class . '::PLAN_PREMIUM') ? Event::PLAN_PREMIUM : 'premium';

        $attrs = [
            'type'       => 'xv',
            'name'       => 'XV Años de Valeria (Demo)',
            'slug'       => 'xv-valeria-demo',
            'status'     => \defined(Event::class . '::STATUS_ACTIVE') ? Event::STATUS_ACTIVE : 'active',
            'event_date' => $eventDate,
            'start_time' => '17:00:00',
            'end_time'   => '01:00:00',

            'theme_key'       => 'xv_modern',
            'primary_color'   => '#a855f7',
            'secondary_color' => '#020617',
            'accent_color'    => '#f97316',
            'font_family'     => 'Playfair Display',

            'modules' => [
                'public_attendance_list' => true,
                'guest_photos_upload'    => true,
                'romantic_phrases'       => false,
                'dress_code'             => true,
                'countdown'              => true,
                'map'                    => true,
                'schedule'               => true,
                'gallery'                => true,
                'songs'                  => true,
                'rsvp'                   => true,
                'gifts'                  => true,
                'story'                  => false, // para probar toggle
            ],

            'settings' => [
                'playlist_enabled'                   => true,
                'playlist_allow_guests_to_add_songs' => true,
                'playlist_max_songs_per_guest'       => 5,
                'playlist_max_votes_per_guest'       => 15,
                'public_show_song_author'            => false,

                'gifts_require_invitation_code'      => true,
                'gifts_allow_unclaim'                => false,
                'gifts_hide_purchased_from_public'   => true,
                'gifts_allow_multi_unit_reserve'     => false,
                'gifts_show_claimers_public'         => false,
                'gifts_max_units_per_guest_per_gift' => 1,

                'guest_photos_max_per_guest'         => 5,
                'guest_photos_auto_approve'          => true,
            ],

            'owner_name'  => 'Familia Demo',
            'owner_email' => 'valeria@example.com',
            'auto_cleanup_after_days' => 90,
        ];

        if (Schema::hasTable('events') && Schema::hasColumn('events', 'plan_key')) {
            $attrs['plan_key'] = $premiumKey;
        }
        if (Schema::hasTable('events') && Schema::hasColumn('events', 'owner_user_id')) {
            $attrs['owner_user_id'] = $this->resolveDemoClientId();
        }

        $xv = Event::query()->updateOrCreate(
            ['slug' => $attrs['slug']],
            $attrs
        );

        $this->purgeEventChildren($xv);

        $this->seedLocations($xv, [
            [
                'type'          => 'reception',
                'name'          => 'Salón de Eventos Luna',
                'address'       => 'Blvd. Ejemplo 456, Ciudad de Ejemplo',
                'maps_url'      => 'https://maps.google.com',
                'display_order' => 1,
            ],
        ]);

        $guests = $this->seedGuests($xv, [
            [
                'name'                => 'Invitado Demo XV',
                'email'               => 'invitado.xv.demo@example.com',
                'invitation_code'     => 'XVDEMO1234',
                'invited_seats'       => 3,
                'rsvp_status'         => Guest::RSVP_YES ?? 'yes',
                'rsvp_public'         => true,
                'guests_confirmed'    => 3,
                'show_in_public_list' => true,
            ],
            [
                'name'                => 'Invitada XV (Pendiente)',
                'email'               => 'invitada.xv.pend@example.com',
                'invitation_code'     => 'XVPEND0001',
                'invited_seats'       => 2,
                'rsvp_status'         => Guest::RSVP_PENDING ?? 'pending',
                'rsvp_public'         => false,
                'guests_confirmed'    => null,
                'show_in_public_list' => false,
            ],
        ]);

        $this->seedSchedules($xv, $eventDate, [
            [
                'title'          => 'Recepción',
                'description'    => 'Bienvenida.',
                'starts_at'      => $eventDate->copy()->setTime(17, 0),
                'ends_at'        => $eventDate->copy()->setTime(18, 0),
                'location_label' => 'Salón de Eventos Luna',
                'location_type'  => 'reception',
                'display_order'  => 1,
            ],
            [
                'title'          => 'Cena',
                'description'    => 'Servicio de cena.',
                'starts_at'      => $eventDate->copy()->setTime(19, 0),
                'ends_at'        => $eventDate->copy()->setTime(20, 0),
                'location_label' => 'Salón de Eventos Luna',
                'location_type'  => 'reception',
                'display_order'  => 2,
            ],
            [
                'title'          => 'Vals',
                'description'    => 'Vals principal.',
                'starts_at'      => $eventDate->copy()->setTime(20, 30),
                'ends_at'        => $eventDate->copy()->setTime(21, 0),
                'location_label' => 'Salón de Eventos Luna',
                'location_type'  => 'reception',
                'display_order'  => 3,
            ],
        ]);

        $this->seedSongs($xv, [
            [
                'title'              => 'Uptown Funk',
                'artist'             => 'Mark Ronson ft. Bruno Mars',
                'message_for_couple' => 'Para prender la fiesta.',
                'suggested_by_guest' => 'XVDEMO1234',
                'show_author'        => false,
                'status'             => \defined(EventSong::class . '::STATUS_APPROVED') ? EventSong::STATUS_APPROVED : 'approved',
            ],
            [
                'title'              => 'Dance Monkey',
                'artist'             => 'Tones and I',
                'message_for_couple' => 'Buen ritmo.',
                'suggested_by_guest' => 'XVDEMO1234',
                'show_author'        => false,
                'status'             => \defined(EventSong::class . '::STATUS_APPROVED') ? EventSong::STATUS_APPROVED : 'approved',
            ],
        ], $guests);

        $this->seedPhotos($xv, [
            [
                'type'           => \defined(EventPhoto::class . '::TYPE_HERO') ? EventPhoto::TYPE_HERO : 'hero',
                'file_path'      => "events/{$xv->id}/photos/originals/hero-xv.svg",
                'thumbnail_path' => null,
                'caption'        => 'Portada XV',
                'status'         => \defined(EventPhoto::class . '::STATUS_APPROVED') ? EventPhoto::STATUS_APPROVED : 'approved',
                'display_order'  => 1,
                'guest_code'     => null,
                'svg_title'      => 'Hero XV',
                'svg_subtitle'   => 'Portada demo',
            ],
            [
                'type'           => \defined(EventPhoto::class . '::TYPE_GALLERY') ? EventPhoto::TYPE_GALLERY : 'gallery',
                'file_path'      => "events/{$xv->id}/photos/originals/xv-1.svg",
                'thumbnail_path' => null,
                'caption'        => 'Sesión de fotos con el vestido',
                'status'         => \defined(EventPhoto::class . '::STATUS_APPROVED') ? EventPhoto::STATUS_APPROVED : 'approved',
                'display_order'  => 1,
                'guest_code'     => null,
                'svg_title'      => 'XV 1',
                'svg_subtitle'   => 'Galería demo',
            ],
            [
                'type'           => \defined(EventPhoto::class . '::TYPE_GUEST_UPLOAD') ? EventPhoto::TYPE_GUEST_UPLOAD : 'guest_upload',
                'file_path'      => "events/{$xv->id}/guest-photos/originals/xv-guest-1.svg",
                'thumbnail_path' => null,
                'caption'        => 'Foto subida por invitado (auto approve ON)',
                'status'         => \defined(EventPhoto::class . '::STATUS_APPROVED') ? EventPhoto::STATUS_APPROVED : 'approved',
                'display_order'  => 1,
                'guest_code'     => 'XVDEMO1234',
                'svg_title'      => 'Guest XV',
                'svg_subtitle'   => 'Auto approve',
            ],
        ], $guests);

        $gifts = $this->seedGifts($xv, [
            [
                'name'          => 'Marco de fotos decorativo',
                'description'   => 'Marco grande para fotos del evento.',
                'store_label'   => 'Amazon',
                'url'           => 'https://example.com/xv-marco',
                'quantity'      => 1,
                'display_order' => 1,
            ],
            [
                'name'          => 'Set de luces decorativas (purchased)',
                'description'   => 'Luces tipo fairy lights.',
                'store_label'   => 'MercadoLibre',
                'url'           => 'https://example.com/xv-luces',
                'quantity'      => 1,
                'display_order' => 2,
            ],
        ]);

        if (isset($gifts['Set de luces decorativas (purchased)'], $guests['XVDEMO1234'])) {
            $this->seedGiftClaim($xv, $gifts['Set de luces decorativas (purchased)'], $guests['XVDEMO1234'], 1, \defined(EventGiftClaim::class.'::STATUS_PURCHASED') ? EventGiftClaim::STATUS_PURCHASED : 'purchased');
            $this->syncGiftFromClaims($gifts['Set de luces decorativas (purchased)']);
        }

        $this->seedDressCodes($xv);
        $this->seedRomanticPhrases($xv);

        // Data existe aunque el módulo esté apagado (útil para panel)
        $this->seedStories($xv);

        return $xv;
    }

    private function seedLocations(Event $event, array $locations): void
    {
        if (!Schema::hasTable('event_locations')) {
            return;
        }

        foreach ($locations as $row) {
            EventLocation::create([
                'event_id'      => $event->id,
                'type'          => $row['type'],
                'name'          => $row['name'],
                'address'       => $row['address'] ?? null,
                'maps_url'      => $row['maps_url'] ?? null,
                'display_order' => (int) ($row['display_order'] ?? 0),
            ]);
        }
    }

    /**
     * Crea invitados y devuelve [invitation_code => Guest]
     */
    private function seedGuests(Event $event, array $guests): array
    {
        if (!Schema::hasTable('guests')) {
            return [];
        }

        $map = [];

        foreach ($guests as $g) {
            $guest = Guest::create([
                'event_id'            => $event->id,
                'name'                => $g['name'],
                'email'               => $g['email'] ?? null,
                'phone'               => $g['phone'] ?? null,
                'invitation_code'     => $g['invitation_code'],
                'invited_seats'       => (int) ($g['invited_seats'] ?? 1),
                'rsvp_status'         => $g['rsvp_status'] ?? (Guest::RSVP_PENDING ?? 'pending'),
                'rsvp_message'        => $g['rsvp_message'] ?? null,
                'rsvp_public'         => (bool) ($g['rsvp_public'] ?? false),
                'guests_confirmed'    => $g['guests_confirmed'] ?? null,
                'show_in_public_list' => (bool) ($g['show_in_public_list'] ?? false),
                'checked_in_at'       => $g['checked_in_at'] ?? null,
            ]);

            $map[$guest->invitation_code] = $guest;
        }

        return $map;
    }

    private function seedSchedules(Event $event, Carbon $eventDate, array $items): void
    {
        if (!Schema::hasTable('event_schedules')) {
            return;
        }

        foreach ($items as $row) {
            EventSchedule::create([
                'event_id'       => $event->id,
                'title'          => $row['title'],
                'description'    => $row['description'] ?? null,
                'starts_at'      => $row['starts_at'],
                'ends_at'        => $row['ends_at'] ?? null,
                'location_label' => $row['location_label'] ?? null,
                'location_type'  => $row['location_type'] ?? null,
                'display_order'  => (int) ($row['display_order'] ?? 0),
            ]);
        }
    }

    /**
     * Crea canciones y devuelve [title => EventSong]
     */
    private function seedSongs(Event $event, array $songs, array $guestsByCode = []): array
    {
        if (!Schema::hasTable('event_songs')) {
            return [];
        }

        $created = [];

        foreach ($songs as $s) {
            $suggestedById = null;
            if (!empty($s['suggested_by_guest']) && isset($guestsByCode[$s['suggested_by_guest']])) {
                $suggestedById = $guestsByCode[$s['suggested_by_guest']]->id;
            }

            $song = EventSong::create([
                'event_id'              => $event->id,
                'title'                 => $s['title'],
                'artist'                => $s['artist'],
                'url'                   => $s['url'] ?? null,
                'message_for_couple'     => $s['message_for_couple'] ?? null,
                'suggested_by_guest_id' => $suggestedById,
                'show_author'           => (bool) ($s['show_author'] ?? false),
                'status'                => $s['status'] ?? (\defined(EventSong::class . '::STATUS_PENDING') ? EventSong::STATUS_PENDING : 'pending'),
                'votes_count'           => 0,
            ]);

            $created[$song->title] = $song;
        }

        return $created;
    }

    private function seedSongVotes(Event $event, EventSong $song, array $votes, array $guestsByCode): void
    {
        if (!Schema::hasTable('song_votes')) {
            return;
        }

        $count = 0;

        foreach ($votes as $v) {
            $code = $v['guest_code'];
            $qty  = (int) ($v['qty'] ?? 1);

            if (!isset($guestsByCode[$code])) {
                continue;
            }

            $guest = $guestsByCode[$code];

            for ($i = 0; $i < $qty; $i++) {
                SongVote::create([
                    'event_id'    => $event->id,
                    'song_id'     => $song->id,
                    'guest_id'    => $guest->id,
                    'fingerprint' => 'seed-' . Str::uuid()->toString(),
                ]);
                $count++;
            }
        }

        $song->update(['votes_count' => $count]);
    }

    /**
     * Crea EventPhoto y también crea un SVG real en storage para que exista el archivo.
     */
    private function seedPhotos(Event $event, array $photos, array $guestsByCode = []): void
    {
        if (!Schema::hasTable('event_photos')) {
            return;
        }

        foreach ($photos as $p) {
            $guestId = null;

            if (!empty($p['guest_code']) && isset($guestsByCode[$p['guest_code']])) {
                $guestId = $guestsByCode[$p['guest_code']]->id;
            }

            // Crear archivo dummy si viene svg_title
            if (!empty($p['file_path']) && !empty($p['svg_title'])) {
                Storage::disk('public')->put(
                    $p['file_path'],
                    $this->buildSimpleSvg($p['svg_title'], $p['svg_subtitle'] ?? '')
                );
            }

            EventPhoto::create([
                'event_id'       => $event->id,
                'guest_id'       => $guestId,
                'type'           => $p['type'],
                'file_path'      => $p['file_path'],
                'thumbnail_path' => $p['thumbnail_path'] ?? null,
                'caption'        => $p['caption'] ?? null,
                'status'         => $p['status'] ?? (\defined(EventPhoto::class . '::STATUS_PENDING') ? EventPhoto::STATUS_PENDING : 'pending'),
                'display_order'  => (int) ($p['display_order'] ?? 0),
            ]);
        }
    }

    /**
     * Crea regalos y devuelve [name => EventGift]
     */
    private function seedGifts(Event $event, array $gifts): array
    {
        if (!Schema::hasTable('event_gifts')) {
            return [];
        }

        $created = [];

        foreach ($gifts as $g) {
            $gift = EventGift::create([
                'event_id'            => $event->id,
                'name'                => $g['name'],
                'description'         => $g['description'] ?? null,
                'store_label'         => $g['store_label'] ?? null,
                'url'                 => $g['url'] ?? null,
                'quantity'            => (int) ($g['quantity'] ?? 1),
                'quantity_reserved'   => 0,
                'status'              => \defined(EventGift::class . '::STATUS_PENDING') ? EventGift::STATUS_PENDING : 'pending',
                'claimed_by_guest_id' => null,
                'reserved_at'         => null,
                'purchased_at'        => null,
                'display_order'       => (int) ($g['display_order'] ?? 0),
            ]);

            $created[$gift->name] = $gift;
        }

        return $created;
    }

    private function seedGiftClaim(Event $event, EventGift $gift, Guest $guest, int $quantity, string $status): ?EventGiftClaim
    {
        if (!Schema::hasTable('event_gift_claims')) {
            return null;
        }

        return EventGiftClaim::create([
            'event_id' => $event->id,
            'gift_id'  => $gift->id,
            'guest_id' => $guest->id,
            'quantity' => max(1, $quantity),
            'status'   => $status,
        ]);
    }

    /**
     * Sincroniza quantity_reserved y status del regalo con base en claims activos.
     */
    private function syncGiftFromClaims(EventGift $gift): void
    {
        if (!Schema::hasTable('event_gift_claims')) {
            return;
        }

        $reserved = \defined(EventGiftClaim::class.'::STATUS_RESERVED') ? EventGiftClaim::STATUS_RESERVED : 'reserved';
        $purchased = \defined(EventGiftClaim::class.'::STATUS_PURCHASED') ? EventGiftClaim::STATUS_PURCHASED : 'purchased';

        $reservedQty = (int) EventGiftClaim::query()
            ->where('gift_id', $gift->id)
            ->whereIn('status', [$reserved, $purchased])
            ->sum('quantity');

        $gift->quantity_reserved = max(0, $reservedQty);

        $hasPurchased = EventGiftClaim::query()
            ->where('gift_id', $gift->id)
            ->where('status', $purchased)
            ->exists();

        $giftPurchasedStatus = \defined(EventGift::class.'::STATUS_PURCHASED') ? EventGift::STATUS_PURCHASED : 'purchased';
        $giftReservedStatus  = \defined(EventGift::class.'::STATUS_RESERVED') ? EventGift::STATUS_RESERVED : 'reserved';
        $giftPendingStatus   = \defined(EventGift::class.'::STATUS_PENDING') ? EventGift::STATUS_PENDING : 'pending';

        if ($hasPurchased) {
            $gift->status = $giftPurchasedStatus;
            $gift->purchased_at = now();
        } elseif ($gift->quantity_reserved > 0) {
            $gift->status = $giftReservedStatus;
            $gift->reserved_at = now();
        } else {
            $gift->status = $giftPendingStatus;
            $gift->reserved_at = null;
            $gift->purchased_at = null;
        }

        $gift->save();
    }

    /*
    |--------------------------------------------------------------------------
    | Miscelánea: Dress code + Romantic phrases
    |--------------------------------------------------------------------------
    */

    private function buildSimpleSvg(string $title, string $subtitle = ''): string
    {
        $t = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $s = htmlspecialchars($subtitle, ENT_QUOTES, 'UTF-8');

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="600" viewBox="0 0 1200 600">
  <defs>
    <linearGradient id="g" x1="0" x2="1" y1="0" y2="1">
      <stop offset="0" stop-color="#0f172a"/>
      <stop offset="1" stop-color="#111827"/>
    </linearGradient>
  </defs>
  <rect width="1200" height="600" fill="url(#g)"/>
  <rect x="60" y="60" width="1080" height="480" rx="36" fill="rgba(255,255,255,0.06)" stroke="rgba(255,255,255,0.10)"/>
  <text x="600" y="290" text-anchor="middle" font-family="Arial, sans-serif" font-size="78" font-weight="700" fill="#f8fafc">
    {$t}
  </text>
  <text x="600" y="360" text-anchor="middle" font-family="Arial, sans-serif" font-size="34" fill="#cbd5e1">
    {$s}
  </text>
</svg>
SVG;
    }

    private function createDressCodeExamplePhoto(Event $event, string $slug, string $title, string $subtitle, int $displayOrder): ?EventPhoto
    {
        if (!Schema::hasTable('event_photos')) {
            return null;
        }

        $typeDressCode = \defined(EventPhoto::class . '::TYPE_DRESS_CODE')
            ? EventPhoto::TYPE_DRESS_CODE
            : 'dress_code';

        $path = "events/{$event->id}/dress-code/examples/{$slug}.svg";
        Storage::disk('public')->put($path, $this->buildSimpleSvg($title, $subtitle));

        return EventPhoto::create([
            'event_id'       => $event->id,
            'guest_id'       => null,
            'type'           => $typeDressCode,
            'file_path'      => $path,
            'thumbnail_path' => null,
            'caption'        => "Ejemplo {$title}",
            'status'         => \defined(EventPhoto::class.'::STATUS_APPROVED') ? EventPhoto::STATUS_APPROVED : 'approved',
            'display_order'  => $displayOrder,
        ]);
    }

    private function seedDressCodes(Event $event): void
    {
        if (!class_exists(EventDressCode::class) || !Schema::hasTable('event_dress_codes')) {
            return;
        }

        $formalPhoto = $this->createDressCodeExamplePhoto($event, 'formal', 'Formal', 'Traje / vestido formal', 1);
        $coctelPhoto = $this->createDressCodeExamplePhoto($event, 'coctel', 'Cóctel', 'Elegante pero cómodo', 2);

        EventDressCode::create([
            'event_id'         => $event->id,
            'title'            => 'Formal',
            'description'      => 'Traje / vestido formal.',
            'examples'         => 'Traje oscuro, vestido largo, zapatos formales.',
            'notes'            => 'Evitar tenis y mezclilla.',
            'icon'             => 'tie',
            'example_photo_id' => $formalPhoto?->id,
            'display_order'    => 1,
            'is_enabled'       => true,
        ]);

        EventDressCode::create([
            'event_id'         => $event->id,
            'title'            => 'Cóctel',
            'description'      => 'Elegante pero cómodo.',
            'examples'         => 'Vestido midi, blazer, zapatos semi-formales.',
            'notes'            => null,
            'icon'             => 'cocktail',
            'example_photo_id' => $coctelPhoto?->id,
            'display_order'    => 2,
            'is_enabled'       => true,
        ]);
    }

    private function seedRomanticPhrases(Event $event): void
    {
        if (!class_exists(EventRomanticPhrase::class) || !Schema::hasTable('event_romantic_phrases')) {
            return;
        }

        EventRomanticPhrase::create([
            'event_id'      => $event->id,
            'phrase'        => 'El amor no se mira, se siente.',
            'author'        => 'Pablo Neruda',
            'display_order' => 1,
            'is_enabled'    => true,
        ]);

        EventRomanticPhrase::create([
            'event_id'      => $event->id,
            'phrase'        => 'Donde hay amor, hay vida.',
            'author'        => 'Mahatma Gandhi',
            'display_order' => 2,
            'is_enabled'    => true,
        ]);

        EventRomanticPhrase::create([
            'event_id'      => $event->id,
            'phrase'        => 'Amar no es mirarse el uno al otro; es mirar juntos en la misma dirección.',
            'author'        => 'Antoine de Saint-Exupéry',
            'display_order' => 3,
            'is_enabled'    => true,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Story
    |--------------------------------------------------------------------------
    */

    private function createStoryExamplePhoto(Event $event, string $slug, string $title, string $subtitle, int $displayOrder): ?EventPhoto
    {
        if (!Schema::hasTable('event_photos')) {
            return null;
        }

        $typeStory = \defined(EventPhoto::class . '::TYPE_STORY')
            ? EventPhoto::TYPE_STORY
            : 'story';

        $path = "events/{$event->id}/story/examples/{$slug}.svg";
        Storage::disk('public')->put($path, $this->buildSimpleSvg($title, $subtitle));

        return EventPhoto::create([
            'event_id'       => $event->id,
            'guest_id'       => null,
            'type'           => $typeStory,
            'file_path'      => $path,
            'thumbnail_path' => null,
            'caption'        => $title,
            'status'         => \defined(EventPhoto::class.'::STATUS_APPROVED') ? EventPhoto::STATUS_APPROVED : 'approved',
            'display_order'  => $displayOrder,
        ]);
    }

    private function seedStories(Event $event): void
    {
        if (!Schema::hasTable('event_stories')) {
            return;
        }

        if ($event->type === 'wedding') {
            $photo1 = $this->createStoryExamplePhoto($event, 'nuestra-historia', 'Nuestra historia', 'Cómo empezó todo', 1);

            EventStory::create([
                'event_id'         => $event->id,
                'title'            => 'Nuestra historia',
                'subtitle'         => 'Cómo empezó todo',
                'body'             => "Nos conocimos en el momento menos esperado.\nDesde ese día, todo empezó a tener sentido.\n\nGracias por ser parte de este día ❤️",
                'example_photo_id' => $photo1?->id,
                'display_order'    => 1,
                'is_enabled'       => true,
            ]);

            $photo2 = $this->createStoryExamplePhoto($event, 'sobre-el-evento', 'Sobre el evento', 'Un día para celebrar', 2);

            EventStory::create([
                'event_id'         => $event->id,
                'title'            => 'Sobre el evento',
                'subtitle'         => 'Un día para celebrar',
                'body'             => "Queremos que se sienta como en casa.\nTraiga ganas de bailar, abrazar y disfrutar.",
                'example_photo_id' => $photo2?->id,
                'display_order'    => 2,
                'is_enabled'       => true,
            ]);

            return;
        }

        // Default (XV / otros)
        $photo = $this->createStoryExamplePhoto($event, 'sobre', 'Sobre', 'Un mensaje especial', 1);

        EventStory::create([
            'event_id'         => $event->id,
            'title'            => 'Sobre',
            'subtitle'         => 'Un mensaje especial',
            'body'             => "Gracias por acompañarnos.\n¡Va a ser una noche increíble!",
            'example_photo_id' => $photo?->id,
            'display_order'    => 1,
            'is_enabled'       => true,
        ]);
    }
}
