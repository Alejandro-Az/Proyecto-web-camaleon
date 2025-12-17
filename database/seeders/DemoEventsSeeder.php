<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventGift;
use App\Models\EventGiftClaim;
use App\Models\EventLocation;
use App\Models\EventPhoto;
use App\Models\EventSchedule;
use App\Models\EventSong;
use App\Models\Guest;
use App\Models\SongVote;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

// Si en su proyecto ya existen estos modelos (por el módulo de miscelánea), úselos.
// Si aún no los tiene en su rama, comente estos 2 use y las llamadas a los métodos
// seedDressCodes/seedRomanticPhrases de abajo.
use App\Models\EventDressCode;
use App\Models\EventRomanticPhrase;

class DemoEventsSeeder extends Seeder
{
    /**
     * Ejecuta el seeder.
     */
    public function run(): void
    {
        $wedding = $this->seedWedding();
        $xv      = $this->seedXv();

        if ($this->command) {
            $this->command->info('✅ DemoEventsSeeder listo.');
            $this->command->info('URLs rápidas para probar:');
            $this->command->info(' - /eventos/'.$wedding->slug.'?i=DEMO1234');
            $this->command->info(' - /eventos/'.$xv->slug.'?i=XVDEMO1234');
            $this->command->info('Tip: para gifts/summary (si aplica), pruebe:');
            $this->command->info(' - /eventos/'.$wedding->slug.'/regalos/resumen?invitation_code=DEMO1234');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Seeders principales
    |--------------------------------------------------------------------------
    */

    private function seedWedding(): Event
    {
        $eventDate = Carbon::create(2025, 12, 31);

        $wedding = Event::create([
            'type'       => 'wedding',
            'name'       => 'Boda de Prueba Ana & Luis',
            'slug'       => 'boda-prueba-ana-luis',
            'status'     => Event::STATUS_ACTIVE,
            'event_date' => $eventDate,
            'start_time' => '18:00:00',
            'end_time'   => '02:00:00',

            'theme_key'       => 'romantic',
            'primary_color'   => '#f472b6',
            'secondary_color' => '#0f172a',
            'accent_color'    => '#f9a8d4',
            'font_family'     => 'Playfair Display',

            // ✅ Guardamos modules SIEMPRE normalizado (defaults + aliases + limpio).
            'modules' => Event::normalizeModulesForStorage([
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
            ]),

            'settings' => [
                // Playlist / canciones
                'playlist_enabled'                   => true,
                'playlist_allow_guests_to_add_songs' => true,
                'playlist_max_songs_per_guest'       => 3,
                'playlist_max_votes_per_guest'       => 10,
                'public_show_song_author'            => true,

                // Regalos
                'gifts_require_invitation_code'      => true,
                'gifts_allow_unclaim'                => true,
                'gifts_hide_purchased_from_public'   => false,
                'gifts_allow_multi_unit_reserve'     => true,
                'gifts_show_claimers_public'         => true,
                'gifts_max_units_per_guest_per_gift' => 2,

                // Fotos de invitados
                'guest_photos_max_per_guest'         => 5,
                'guest_photos_auto_approve'          => false,
            ],

            'owner_name'  => 'Ana & Luis',
            'owner_email' => 'ana.y.luis@example.com',

            'auto_cleanup_after_days' => 60,
        ]);

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

        $guests = $this->seedGuests($wedding, [
            [
                'name'                => 'Invitado Demo',
                'email'               => 'invitado.demo@example.com',
                'invitation_code'     => 'DEMO1234',
                'invited_seats'       => 2,
                'rsvp_status'         => Guest::RSVP_PENDING,
                'rsvp_public'         => false,
                'guests_confirmed'    => null,
                'show_in_public_list' => false,
            ],
            [
                'name'                => 'María Confirmada',
                'email'               => 'maria.confirmada@example.com',
                'invitation_code'     => 'MARIAYES1',
                'invited_seats'       => 2,
                'rsvp_status'         => Guest::RSVP_YES,
                'rsvp_public'         => true,
                'guests_confirmed'    => 2,
                'show_in_public_list' => true,
            ],
            [
                'name'                => 'Carlos No Asiste',
                'email'               => 'carlos.no@example.com',
                'invitation_code'     => 'CARLOSNO1',
                'invited_seats'       => 1,
                'rsvp_status'         => Guest::RSVP_NO,
                'rsvp_public'         => false,
                'guests_confirmed'    => 0,
                'show_in_public_list' => false,
            ],
        ]);

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

        $songs = $this->seedSongs($wedding, [
            [
                'title'              => 'Perfect',
                'artist'             => 'Ed Sheeran',
                'url'                => 'https://open.spotify.com/track/0tgVpDi06FyKpA1z0VMD4v',
                'message_for_couple' => 'Para el primer baile, obvio 💕',
                'suggested_by_guest' => 'DEMO1234',
                'show_author'        => true,
                'status'             => EventSong::STATUS_APPROVED,
            ],
            [
                'title'              => 'Can\'t Help Falling in Love',
                'artist'             => 'Elvis Presley',
                'url'                => null,
                'message_for_couple' => 'Para el vals con los papás.',
                'suggested_by_guest' => 'MARIAYES1',
                'show_author'        => true,
                'status'             => EventSong::STATUS_APPROVED,
            ],
            [
                'title'              => 'Marry You',
                'artist'             => 'Bruno Mars',
                'url'                => null,
                'message_for_couple' => 'Para levantar a todos de las mesas.',
                'suggested_by_guest' => 'DEMO1234',
                'show_author'        => true,
                'status'             => EventSong::STATUS_APPROVED,
            ],
            [
                'title'              => 'A Thousand Years',
                'artist'             => 'Christina Perri',
                'url'                => null,
                'message_for_couple' => 'Clásico romántico.',
                'suggested_by_guest' => 'DEMO1234',
                'show_author'        => false,
                'status'             => EventSong::STATUS_PENDING,
            ],
        ], $guests);

        $this->seedSongVotes($wedding, $songs['Perfect'], [
            ['guest_code' => 'DEMO1234', 'qty' => 1],
            ['guest_code' => 'MARIAYES1', 'qty' => 1],
        ], $guests);

        $this->seedPhotos($wedding, [
            [
                'type'           => EventPhoto::TYPE_HERO,
                'file_path'      => 'events/'.$wedding->id.'/photos/originals/hero-boda.jpg',
                'thumbnail_path' => null,
                'caption'        => 'Portada del evento',
                'status'         => EventPhoto::STATUS_APPROVED,
                'display_order'  => 1,
                'guest_code'     => null,
            ],
            [
                'type'           => EventPhoto::TYPE_GALLERY,
                'file_path'      => 'events/'.$wedding->id.'/photos/originals/boda-1.jpg',
                'thumbnail_path' => 'events/'.$wedding->id.'/photos/thumbnails/boda-1_thumb.jpg',
                'caption'        => 'Sesión de fotos de compromiso',
                'status'         => EventPhoto::STATUS_APPROVED,
                'display_order'  => 1,
                'guest_code'     => null,
            ],
            [
                'type'           => EventPhoto::TYPE_GALLERY,
                'file_path'      => 'events/'.$wedding->id.'/photos/originals/boda-2.jpg',
                'thumbnail_path' => 'events/'.$wedding->id.'/photos/thumbnails/boda-2_thumb.jpg',
                'caption'        => 'Momento especial durante la ceremonia',
                'status'         => EventPhoto::STATUS_APPROVED,
                'display_order'  => 2,
                'guest_code'     => null,
            ],
            [
                'type'           => EventPhoto::TYPE_GALLERY,
                'file_path'      => 'events/'.$wedding->id.'/photos/originals/boda-3.jpg',
                'thumbnail_path' => 'events/'.$wedding->id.'/photos/thumbnails/boda-3_thumb.jpg',
                'caption'        => 'Fiesta con amigos y familiares',
                'status'         => EventPhoto::STATUS_APPROVED,
                'display_order'  => 3,
                'guest_code'     => null,
            ],
            [
                'type'           => EventPhoto::TYPE_GUEST_UPLOAD,
                'file_path'      => 'events/'.$wedding->id.'/guest-photos/originals/guest-1.jpg',
                'thumbnail_path' => null,
                'caption'        => 'Selfie en la entrada 😄',
                'status'         => EventPhoto::STATUS_APPROVED,
                'display_order'  => 1,
                'guest_code'     => 'DEMO1234',
            ],
            [
                'type'           => EventPhoto::TYPE_GUEST_UPLOAD,
                'file_path'      => 'events/'.$wedding->id.'/guest-photos/originals/guest-2.jpg',
                'thumbnail_path' => null,
                'caption'        => 'Foto pendiente de aprobar',
                'status'         => EventPhoto::STATUS_PENDING,
                'display_order'  => 2,
                'guest_code'     => 'MARIAYES1',
            ],
        ], $guests);

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

        $this->seedGiftClaim($wedding, $gifts['Juego de platos x2'], $guests['MARIAYES1'], 1, EventGiftClaim::STATUS_RESERVED);
        $this->seedGiftClaim($wedding, $gifts['Tostador eléctrico'], $guests['DEMO1234'], 1, EventGiftClaim::STATUS_PURCHASED);
        $this->seedGiftClaim($wedding, $gifts['Juego de copas (multi-unidad)'], $guests['DEMO1234'], 2, EventGiftClaim::STATUS_RESERVED);
        $this->seedGiftClaim($wedding, $gifts['Juego de copas (multi-unidad)'], $guests['MARIAYES1'], 1, EventGiftClaim::STATUS_RESERVED);

        $this->syncGiftFromClaims($gifts['Juego de platos x2']);
        $this->syncGiftFromClaims($gifts['Tostador eléctrico']);
        $this->syncGiftFromClaims($gifts['Juego de copas (multi-unidad)']);

        // Módulos “miscelánea”
        $this->seedDressCodes($wedding);
        $this->seedRomanticPhrases($wedding);

        return $wedding;
    }

    private function seedXv(): Event
    {
        $eventDate = Carbon::create(2026, 5, 15);

        $xv = Event::create([
            'type'       => 'xv',
            'name'       => 'XV Años de Valeria (Demo)',
            'slug'       => 'xv-valeria-demo',
            'status'     => Event::STATUS_ACTIVE,
            'event_date' => $eventDate,
            'start_time' => '17:00:00',
            'end_time'   => '01:00:00',

            'theme_key'       => 'xv_modern',
            'primary_color'   => '#a855f7',
            'secondary_color' => '#020617',
            'accent_color'    => '#f97316',
            'font_family'     => 'Playfair Display',

            'modules' => Event::normalizeModulesForStorage([
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
            ]),

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
        ]);

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
                'rsvp_status'         => Guest::RSVP_YES,
                'rsvp_public'         => true,
                'guests_confirmed'    => 3,
                'show_in_public_list' => true,
            ],
            [
                'name'                => 'Invitada XV (Pendiente)',
                'email'               => 'invitada.xv.pend@example.com',
                'invitation_code'     => 'XVPEND0001',
                'invited_seats'       => 2,
                'rsvp_status'         => Guest::RSVP_PENDING,
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
                'url'                => null,
                'message_for_couple' => 'Para prender la fiesta.',
                'suggested_by_guest' => 'XVDEMO1234',
                'show_author'        => false,
                'status'             => EventSong::STATUS_APPROVED,
            ],
            [
                'title'              => 'Dance Monkey',
                'artist'             => 'Tones and I',
                'url'                => null,
                'message_for_couple' => 'Buen ritmo.',
                'suggested_by_guest' => 'XVDEMO1234',
                'show_author'        => false,
                'status'             => EventSong::STATUS_APPROVED,
            ],
        ], $guests);

        $this->seedPhotos($xv, [
            [
                'type'           => EventPhoto::TYPE_HERO,
                'file_path'      => 'events/'.$xv->id.'/photos/originals/hero-xv.jpg',
                'thumbnail_path' => null,
                'caption'        => 'Portada XV',
                'status'         => EventPhoto::STATUS_APPROVED,
                'display_order'  => 1,
                'guest_code'     => null,
            ],
            [
                'type'           => EventPhoto::TYPE_GALLERY,
                'file_path'      => 'events/'.$xv->id.'/photos/originals/xv-1.jpg',
                'thumbnail_path' => 'events/'.$xv->id.'/photos/thumbnails/xv-1_thumb.jpg',
                'caption'        => 'Sesión de fotos con el vestido',
                'status'         => EventPhoto::STATUS_APPROVED,
                'display_order'  => 1,
                'guest_code'     => null,
            ],
            [
                'type'           => EventPhoto::TYPE_GALLERY,
                'file_path'      => 'events/'.$xv->id.'/photos/originals/xv-2.jpg',
                'thumbnail_path' => 'events/'.$xv->id.'/photos/thumbnails/xv-2_thumb.jpg',
                'caption'        => 'Entrada al salón',
                'status'         => EventPhoto::STATUS_APPROVED,
                'display_order'  => 2,
                'guest_code'     => null,
            ],
            [
                'type'           => EventPhoto::TYPE_GUEST_UPLOAD,
                'file_path'      => 'events/'.$xv->id.'/guest-photos/originals/xv-guest-1.jpg',
                'thumbnail_path' => null,
                'caption'        => 'Foto subida por invitado (auto approve ON)',
                'status'         => EventPhoto::STATUS_APPROVED,
                'display_order'  => 1,
                'guest_code'     => 'XVDEMO1234',
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

        $this->seedGiftClaim($xv, $gifts['Set de luces decorativas (purchased)'], $guests['XVDEMO1234'], 1, EventGiftClaim::STATUS_PURCHASED);
        $this->syncGiftFromClaims($gifts['Set de luces decorativas (purchased)']);

        // Módulos “miscelánea”
        $this->seedDressCodes($xv);
        $this->seedRomanticPhrases($xv);

        return $xv;
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers (funciones) para mantener el seeder limpio y testeable
    |--------------------------------------------------------------------------
    */

    private function seedLocations(Event $event, array $locations): void
    {
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
     * Crea invitados y devuelve un mapa [invitation_code => Guest]
     */
    private function seedGuests(Event $event, array $guests): array
    {
        $map = [];

        foreach ($guests as $g) {
            $guest = Guest::create([
                'event_id'            => $event->id,
                'name'                => $g['name'],
                'email'               => $g['email'] ?? null,
                'phone'               => $g['phone'] ?? null,
                'invitation_code'     => $g['invitation_code'],
                'invited_seats'       => (int) ($g['invited_seats'] ?? 1),
                'rsvp_status'         => $g['rsvp_status'] ?? Guest::RSVP_PENDING,
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
        foreach ($items as $row) {
            EventSchedule::create([
                'event_id'        => $event->id,
                'title'           => $row['title'],
                'description'     => $row['description'] ?? null,
                'starts_at'       => $row['starts_at'],
                'ends_at'         => $row['ends_at'] ?? null,
                'location_label'  => $row['location_label'] ?? null,
                'location_type'   => $row['location_type'] ?? null,
                'display_order'   => (int) ($row['display_order'] ?? 0),
            ]);
        }
    }

    /**
     * Crea canciones y devuelve mapa [title => EventSong] (para poder votar fácil)
     */
    private function seedSongs(Event $event, array $songs, array $guestsByCode = []): array
    {
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
                'message_for_couple'    => $s['message_for_couple'] ?? null,
                'suggested_by_guest_id' => $suggestedById,
                'show_author'           => (bool) ($s['show_author'] ?? false),
                'status'                => $s['status'] ?? EventSong::STATUS_PENDING,
                'votes_count'           => 0,
            ]);

            $created[$song->title] = $song;
        }

        return $created;
    }

    private function seedSongVotes(Event $event, EventSong $song, array $votes, array $guestsByCode): void
    {
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
                    'fingerprint' => 'seed-'.Str::uuid()->toString(),
                ]);

                $count++;
            }
        }

        $song->update(['votes_count' => $count]);
    }

    private function seedPhotos(Event $event, array $photos, array $guestsByCode = []): void
    {
        foreach ($photos as $p) {
            $guestId = null;

            if (!empty($p['guest_code']) && isset($guestsByCode[$p['guest_code']])) {
                $guestId = $guestsByCode[$p['guest_code']]->id;
            }

            EventPhoto::create([
                'event_id'       => $event->id,
                'guest_id'       => $guestId,
                'type'           => $p['type'],
                'file_path'      => $p['file_path'],
                'thumbnail_path' => $p['thumbnail_path'] ?? null,
                'caption'        => $p['caption'] ?? null,
                'status'         => $p['status'] ?? EventPhoto::STATUS_PENDING,
                'display_order'  => (int) ($p['display_order'] ?? 0),
            ]);
        }
    }

    /**
     * Crea regalos y devuelve mapa [name => EventGift]
     */
    private function seedGifts(Event $event, array $gifts): array
    {
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
                'status'              => EventGift::STATUS_PENDING,
                'claimed_by_guest_id' => null,
                'reserved_at'         => null,
                'purchased_at'        => null,
                'display_order'       => (int) ($g['display_order'] ?? 0),
            ]);

            $created[$gift->name] = $gift;
        }

        return $created;
    }

    private function seedGiftClaim(Event $event, EventGift $gift, Guest $guest, int $quantity, string $status): EventGiftClaim
    {
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
        $reservedQty = (int) EventGiftClaim::query()
            ->where('gift_id', $gift->id)
            ->whereIn('status', [EventGiftClaim::STATUS_RESERVED, EventGiftClaim::STATUS_PURCHASED])
            ->sum('quantity');

        $gift->quantity_reserved = max(0, $reservedQty);

        $hasPurchased = EventGiftClaim::query()
            ->where('gift_id', $gift->id)
            ->where('status', EventGiftClaim::STATUS_PURCHASED)
            ->exists();

        if ($hasPurchased) {
            $gift->status = EventGift::STATUS_PURCHASED;
            $gift->purchased_at = now();
        } elseif ($gift->quantity_reserved > 0) {
            $gift->status = EventGift::STATUS_RESERVED;
            $gift->reserved_at = now();
        } else {
            $gift->status = EventGift::STATUS_PENDING;
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

    /**
     * Crea un SVG simple (texto) para que la imagen exista de verdad en storage
     * y usted lo vea en el navegador sin tener que subir archivos a mano.
     */
    private function buildDressCodeSvg(string $title, string $subtitle = ''): string
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

    /**
     * Crea un EventPhoto tipo dress_code y guarda el archivo real en storage/public.
     */
    private function createDressCodeExamplePhoto(Event $event, string $slug, string $title, string $subtitle, int $displayOrder): EventPhoto
    {
        $typeDressCode = \defined(EventPhoto::class.'::TYPE_DRESS_CODE')
            ? EventPhoto::TYPE_DRESS_CODE
            : 'dress_code';

        $path = "events/{$event->id}/dress-code/examples/{$slug}.svg";

        Storage::disk('public')->put($path, $this->buildDressCodeSvg($title, $subtitle));

        return EventPhoto::create([
            'event_id'       => $event->id,
            'guest_id'       => null,
            'type'           => $typeDressCode,
            'file_path'      => $path,
            'thumbnail_path' => null,
            'caption'        => "Ejemplo {$title}",
            'status'         => EventPhoto::STATUS_APPROVED,
            'display_order'  => $displayOrder,
        ]);
    }

    private function seedDressCodes(Event $event): void
    {
        // 1) Creamos 2 fotos reales (SVG) en storage/public y sus registros en event_photos
        $formalPhoto = $this->createDressCodeExamplePhoto(
            $event,
            'formal',
            'Formal',
            'Traje / vestido formal',
            1
        );

        $coctelPhoto = $this->createDressCodeExamplePhoto(
            $event,
            'coctel',
            'Cóctel',
            'Elegante pero cómodo',
            2
        );

        // 2) Creamos los dress codes apuntando a esas fotos (example_photo_id)
        EventDressCode::create([
            'event_id'         => $event->id,
            'title'            => 'Formal',
            'description'      => 'Traje / vestido formal.',
            'examples'         => 'Traje oscuro, vestido largo, zapatos formales.',
            'notes'            => 'Evitar tenis y mezclilla.',
            'icon'             => 'tie',
            'example_photo_id' => $formalPhoto->id,
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
            'example_photo_id' => $coctelPhoto->id,
            'display_order'    => 2,
            'is_enabled'       => true,
        ]);
    }

    private function seedRomanticPhrases(Event $event): void
    {
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
}
