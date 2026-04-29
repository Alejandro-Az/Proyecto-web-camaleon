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
use App\Models\EventDressCode;
use App\Models\EventRomanticPhrase;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DemoEventsSeeder extends Seeder
{
    private ?int $demoClientId = null;

    public function run(): void
    {
        Storage::disk('public');

        $wedding = $this->seedWedding();
        $xv      = $this->seedXv();
        $corp    = $this->seedCorp();

        if ($this->command) {
            $this->command->info('✅ DemoEventsSeeder listo — 3 eventos.');
            $this->command->info('');
            $this->command->info('🌿 Boda Sofía & Mateo (todos los módulos ON):');
            $this->command->info('   /eventos/' . $wedding->slug . '?i=SOFIA0001');
            $this->command->info('');
            $this->command->info('🌸 XV Valeria (schedule, story, dress_code OFF):');
            $this->command->info('   /eventos/' . $xv->slug . '?i=VALE0001');
            $this->command->info('');
            $this->command->info('🏢 Cena Corporativa (songs, gallery, countdown, story, dress_code OFF):');
            $this->command->info('   /eventos/' . $corp->slug . '?i=CORP0001');
        }
    }

    // ────────────────────────────────────────────────────────────────
    // EVENTO 1 — Boda Sofía & Mateo — TODOS los módulos ON — tuscan
    // ────────────────────────────────────────────────────────────────

    private function seedWedding(): Event
    {
        $eventDate = Carbon::create(2026, 9, 12);

        $attrs = [
            'type'       => 'wedding',
            'name'       => 'Sofía & Mateo',
            'slug'       => 'boda-sofia-mateo',
            'status'     => Event::STATUS_ACTIVE,
            'event_date' => $eventDate,
            'start_time' => '16:00:00',
            'end_time'   => '03:00:00',
            'plan_key'   => Event::PLAN_PREMIUM,
            'owner_name'  => 'Sofía & Mateo',
            'owner_email' => 'sofia.mateo.boda@camaleon.test',
            'auto_cleanup_after_days' => 60,

            'modules' => [
                'countdown'              => true,
                'story'                  => true,
                'schedule'               => true,
                'dress_code'             => true,
                'rsvp'                   => true,
                'gifts'                  => true,
                'songs'                  => true,
                'gallery'                => true,
                'guest_photos_upload'    => true,
                'romantic_phrases'       => true,
                'public_attendance_list' => true,
                'map'                    => true,
            ],

            'settings' => [
                'theme_palette'                      => 'tuscan',
                'story_intro'                        => 'Después de cinco años, una pandemia, dos mudanzas y un perro, decidimos hacerlo oficial.',

                'playlist_enabled'                   => true,
                'playlist_allow_guests_to_add_songs' => true,
                'playlist_max_songs_per_guest'       => 3,
                'playlist_max_votes_per_guest'       => 5,
                'public_show_song_author'            => true,

                'gifts_require_invitation_code'      => false,
                'gifts_allow_unclaim'                => true,
                'gifts_hide_purchased_from_public'   => false,
                'gifts_allow_multi_unit_reserve'     => true,
                'gifts_show_claimers_public'         => true,
                'gifts_max_units_per_guest_per_gift' => 2,

                'guest_photos_max_per_guest'         => 10,
                'guest_photos_auto_approve'          => false,

                'countdown_expired_label'            => '¡Hoy es el gran día!',
            ],
        ];

        if (Schema::hasColumn('events', 'owner_user_id')) {
            $attrs['owner_user_id'] = $this->resolveDemoClientId();
        }

        $wedding = Event::query()->updateOrCreate(['slug' => $attrs['slug']], $attrs);
        $this->purgeEventChildren($wedding);

        // Ubicaciones
        $this->seedLocations($wedding, [
            ['type' => 'ceremony',  'name' => 'Capilla del Olivar',           'address' => 'Hacienda San Gabriel de Barrera, Guanajuato',  'maps_url' => 'https://maps.google.com', 'display_order' => 1],
            ['type' => 'reception', 'name' => 'Jardín Toscana — Hacienda SGB','address' => 'Camino a la Hacienda S/N, Marfil, Gto.',         'maps_url' => 'https://maps.google.com', 'display_order' => 2],
            ['type' => 'other',     'name' => 'Estacionamiento VIP',           'address' => 'Acceso sur de la hacienda',                    'maps_url' => null,                      'display_order' => 3],
        ]);

        // Invitados
        $guests = $this->seedGuests($wedding, [
            ['name' => 'Andrea Padilla Hernández', 'email' => 'andrea.p@demo.mx',  'invitation_code' => 'SOFIA0001', 'invited_seats' => 2, 'rsvp_status' => Guest::RSVP_PENDING, 'guests_confirmed' => null,  'show_in_public_list' => false],
            ['name' => 'Lucía Méndez Torres',      'email' => 'lucia.m@demo.mx',   'invitation_code' => 'SOFIA0002', 'invited_seats' => 2, 'rsvp_status' => Guest::RSVP_YES,     'guests_confirmed' => 2,     'show_in_public_list' => true,  'rsvp_message' => '¡No puedo esperar! Los amo ❤️'],
            ['name' => 'Roberto Villanueva García', 'email' => 'rob.v@demo.mx',    'invitation_code' => 'SOFIA0003', 'invited_seats' => 3, 'rsvp_status' => Guest::RSVP_YES,     'guests_confirmed' => 3,     'show_in_public_list' => true,  'rsvp_message' => 'Familia completa con emoción.'],
            ['name' => 'Daniela Cruz Morales',      'email' => 'dani.c@demo.mx',   'invitation_code' => 'SOFIA0004', 'invited_seats' => 1, 'rsvp_status' => Guest::RSVP_NO,      'guests_confirmed' => 0,     'show_in_public_list' => false, 'rsvp_message' => 'Mil disculpas, estaré de viaje.'],
            ['name' => 'Fernando Ríos Sandoval',    'email' => 'fer.r@demo.mx',    'invitation_code' => 'SOFIA0005', 'invited_seats' => 2, 'rsvp_status' => Guest::RSVP_MAYBE,   'guests_confirmed' => null,  'show_in_public_list' => false],
        ]);

        // Itinerario
        $this->seedSchedules($wedding, $eventDate, [
            ['title' => 'Recepción de invitados',  'description' => 'Llegada y bienvenida con cóctel de frutas de temporada.',                                        'starts_at' => $eventDate->copy()->setTime(15, 30), 'ends_at' => $eventDate->copy()->setTime(16, 0),  'location_label' => 'Jardín de los Cipreses',    'display_order' => 1],
            ['title' => 'Ceremonia religiosa',      'description' => 'Unión en la capilla privada de la hacienda. Recepción de invitados desde las 15:30.',           'starts_at' => $eventDate->copy()->setTime(16, 0),  'ends_at' => $eventDate->copy()->setTime(17, 15), 'location_label' => 'Capilla del Olivar',         'display_order' => 2],
            ['title' => 'Cóctel de bienvenida',    'description' => 'Mezcales artesanales de Oaxaca, vino tinto de la región y antipasti de quesos locales.',        'starts_at' => $eventDate->copy()->setTime(17, 30), 'ends_at' => $eventDate->copy()->setTime(19, 0),  'location_label' => 'Jardín de los Cipreses',    'display_order' => 3],
            ['title' => 'Cena de gala',             'description' => 'Menú degustación a cuatro tiempos por el Chef Renata Ortiz. Vegano disponible.',               'starts_at' => $eventDate->copy()->setTime(19, 15), 'ends_at' => $eventDate->copy()->setTime(21, 0),  'location_label' => 'Salón Toscana',             'display_order' => 4],
            ['title' => 'Brindis & primer baile',  'description' => 'Acompáñanos a brindar por el inicio de esta nueva etapa.',                                      'starts_at' => $eventDate->copy()->setTime(21, 0),  'ends_at' => $eventDate->copy()->setTime(21, 30), 'location_label' => 'Pérgola principal',         'display_order' => 5],
            ['title' => 'Fiesta & baile abierto',  'description' => 'DJ Marisol & banda en vivo. Pista abierta hasta las 3:00 am bajo las estrellas.',              'starts_at' => $eventDate->copy()->setTime(21, 30), 'ends_at' => $eventDate->copy()->addDay()->setTime(3, 0), 'location_label' => 'Pista bajo las estrellas', 'display_order' => 6],
        ]);

        // Canciones
        $songs = $this->seedSongs($wedding, [
            ['title' => 'Perfect',                    'artist' => 'Ed Sheeran',              'message_for_couple' => 'Para el primer baile, obvio 💕',               'suggested_by_guest' => 'SOFIA0002', 'show_author' => true,  'status' => 'approved'],
            ['title' => 'Can\'t Help Falling in Love','artist' => 'Elvis Presley',            'message_for_couple' => 'Para el vals con los papás, clásico eterno.',  'suggested_by_guest' => 'SOFIA0003', 'show_author' => true,  'status' => 'approved'],
            ['title' => 'Marry You',                  'artist' => 'Bruno Mars',               'message_for_couple' => 'Para levantar a todos de las mesas 🎉',        'suggested_by_guest' => 'SOFIA0001', 'show_author' => true,  'status' => 'approved'],
            ['title' => 'A Thousand Years',           'artist' => 'Christina Perri',          'message_for_couple' => 'Romántica y perfecta para ustedes.',           'suggested_by_guest' => 'SOFIA0002', 'show_author' => false, 'status' => 'approved'],
            ['title' => 'Vivir mi vida',              'artist' => 'Marc Anthony',             'message_for_couple' => '¡A bailar salsa! 💃',                          'suggested_by_guest' => 'SOFIA0003', 'show_author' => true,  'status' => 'approved'],
            ['title' => 'September',                  'artist' => 'Earth, Wind & Fire',       'message_for_couple' => 'Clásico para abrir la pista.',                 'suggested_by_guest' => 'SOFIA0005', 'show_author' => true,  'status' => 'approved'],
        ], $guests);

        // Votos
        if (isset($songs['Perfect'])) {
            $this->seedSongVotes($wedding, $songs['Perfect'], [['guest_code' => 'SOFIA0002', 'qty' => 1], ['guest_code' => 'SOFIA0003', 'qty' => 1]], $guests);
        }
        if (isset($songs['Vivir mi vida'])) {
            $this->seedSongVotes($wedding, $songs['Vivir mi vida'], [['guest_code' => 'SOFIA0003', 'qty' => 1], ['guest_code' => 'SOFIA0005', 'qty' => 1]], $guests);
        }
        if (isset($songs['Marry You'])) {
            $this->seedSongVotes($wedding, $songs['Marry You'], [['guest_code' => 'SOFIA0001', 'qty' => 1]], $guests);
        }

        // Fotos (galería + hero)
        $this->seedPhotos($wedding, [
            ['type' => 'hero',         'file_path' => "events/{$wedding->id}/photos/originals/hero.jpg",      'image_url' => 'https://picsum.photos/seed/hacienda-arch/1200/700',   'caption' => null,                           'display_order' => 1, 'guest_code' => null,        'svg_title' => 'Sofía & Mateo',  'svg_subtitle' => 'San Miguel de Allende',  'svg_palette' => 'tuscan'],
            ['type' => 'gallery',      'file_path' => "events/{$wedding->id}/photos/originals/gal-1.jpg",    'image_url' => 'https://picsum.photos/seed/tropical-cenote/800/600',  'caption' => 'Propuesta en Yucatán',          'display_order' => 1, 'guest_code' => null,        'svg_title' => 'Propuesta',      'svg_subtitle' => 'Mérida, Yucatán',        'svg_palette' => 'tuscan'],
            ['type' => 'gallery',      'file_path' => "events/{$wedding->id}/photos/originals/gal-2.jpg",    'image_url' => 'https://picsum.photos/seed/city-romance-cdmx/800/600','caption' => 'Sesión de compromiso en CDMX',  'display_order' => 2, 'guest_code' => null,        'svg_title' => 'Compromiso',     'svg_subtitle' => 'Ciudad de México',       'svg_palette' => 'tuscan'],
            ['type' => 'gallery',      'file_path' => "events/{$wedding->id}/photos/originals/gal-3.jpg",    'image_url' => 'https://picsum.photos/seed/garden-ceremony/800/600',  'caption' => 'Save the date en la hacienda', 'display_order' => 3, 'guest_code' => null,        'svg_title' => 'Save the Date', 'svg_subtitle' => 'Hacienda San Gabriel',   'svg_palette' => 'tuscan'],
            ['type' => 'gallery',      'file_path' => "events/{$wedding->id}/photos/originals/gal-4.jpg",    'image_url' => 'https://picsum.photos/seed/colonial-alley/800/600',   'caption' => 'Pre-boda en San Miguel',        'display_order' => 4, 'guest_code' => null,        'svg_title' => 'Pre-boda',      'svg_subtitle' => 'San Miguel de Allende',  'svg_palette' => 'tuscan'],
            ['type' => 'gallery',      'file_path' => "events/{$wedding->id}/photos/originals/gal-5.jpg",    'image_url' => 'https://picsum.photos/seed/vineyard-hills/800/600',   'caption' => 'Viaje a la Toscana italiana',  'display_order' => 5, 'guest_code' => null,        'svg_title' => 'Toscana',       'svg_subtitle' => 'Italia · Inspiración',   'svg_palette' => 'tuscan'],
            ['type' => 'guest_upload', 'file_path' => "events/{$wedding->id}/guest-photos/originals/g1.jpg", 'image_url' => 'https://picsum.photos/seed/wedding-entrance/600/600', 'caption' => 'Selfie en la entrada 😄',       'display_order' => 1, 'guest_code' => 'SOFIA0001', 'svg_title' => 'Foto Invitado', 'svg_subtitle' => 'Andrea · Entrada',       'svg_palette' => 'tuscan'],
            ['type' => 'guest_upload', 'file_path' => "events/{$wedding->id}/guest-photos/originals/g2.jpg", 'image_url' => 'https://picsum.photos/seed/candlelight-dinner/600/600','caption' => 'Mesa con la familia 🥂',        'display_order' => 2, 'guest_code' => 'SOFIA0003', 'svg_title' => 'Foto Invitado', 'svg_subtitle' => 'Roberto · Mesa familiar','svg_palette' => 'tuscan'],
        ], $guests);

        // Regalos
        $gifts = $this->seedGifts($wedding, [
            ['name' => 'Vajilla de gres artesanal (6 pzas)',  'description' => 'Vajilla hecha a mano por artesanos de Tonalá. Incluye platos, tazones y soperos.', 'store_label' => 'Casa Palacio',     'url' => null, 'quantity' => 1, 'display_order' => 1],
            ['name' => 'Set copas cristal Bohemia (12 pzas)', 'description' => 'Copas para vino tinto, blanco y champagne. Estilo europeo clásico.',               'store_label' => 'El Palacio de Hierro', 'url' => null, 'quantity' => 1, 'display_order' => 2],
            ['name' => 'Mantelería de lino natural',          'description' => 'Mantel y 8 servilletas de lino belga color hueso. Lavable.',                       'store_label' => 'Williams Sonoma',  'url' => null, 'quantity' => 1, 'display_order' => 3],
            ['name' => 'Ánforas de barro oaxaqueño (par)',    'description' => 'Ánforas decorativas pintadas a mano en Oaxaca. Únicos y especiales.',              'store_label' => 'Mercado de artesanías', 'url' => null, 'quantity' => 2, 'display_order' => 4],
            ['name' => 'Tabla de servir madera de olivo',     'description' => 'Tabla grande para quesos y embutidos. Madera de olivo italiana.',                  'store_label' => 'Pottery Barn',     'url' => null, 'quantity' => 3, 'display_order' => 5],
            ['name' => 'Aportación libre (sobre o transferencia)', 'description' => 'Cualquier monto es bienvenido y muy apreciado. ¡Gracias!',                   'store_label' => null,               'url' => null, 'quantity' => 99, 'display_order' => 6],
        ]);

        if (isset($gifts['Set copas cristal Bohemia (12 pzas)'], $guests['SOFIA0002'])) {
            $this->seedGiftClaim($wedding, $gifts['Set copas cristal Bohemia (12 pzas)'], $guests['SOFIA0002'], 1, 'reserved');
            $this->syncGiftFromClaims($gifts['Set copas cristal Bohemia (12 pzas)']);
        }
        if (isset($gifts['Mantelería de lino natural'], $guests['SOFIA0003'])) {
            $this->seedGiftClaim($wedding, $gifts['Mantelería de lino natural'], $guests['SOFIA0003'], 1, 'purchased');
            $this->syncGiftFromClaims($gifts['Mantelería de lino natural']);
        }
        if (isset($gifts['Tabla de servir madera de olivo'], $guests['SOFIA0002'], $guests['SOFIA0003'])) {
            $this->seedGiftClaim($wedding, $gifts['Tabla de servir madera de olivo'], $guests['SOFIA0002'], 1, 'reserved');
            $this->seedGiftClaim($wedding, $gifts['Tabla de servir madera de olivo'], $guests['SOFIA0003'], 1, 'reserved');
            $this->syncGiftFromClaims($gifts['Tabla de servir madera de olivo']);
        }

        // Dress codes
        $this->seedDressCodesDetailed($wedding, [
            ['title' => 'Damas',      'description' => 'Vestido largo o midi formal. Tonos tierra, marfil, oliva o coñac. Sin blanco ni negro.',          'icon' => 'dress',    'display_order' => 1],
            ['title' => 'Caballeros', 'description' => 'Traje formal en tonos arena, beige u olivo. Corbata opcional. Evitar negro y azul marino.',       'icon' => 'suit',     'display_order' => 2],
            ['title' => 'Calzado',    'description' => 'Ceremonia y jardín sobre césped — recomendamos tacón de bloque o cuña. Stilettos no recomendados.', 'icon' => 'footprints', 'display_order' => 3],
        ]);

        // Historia
        $this->seedStoriesDetailed($wedding, [
            ['title' => 'Cómo nos conocimos',       'subtitle' => 'Una historia de libros', 'body' => "Nos conocimos en la librería más pequeña de Roma.\nÉl buscaba Calvino. Ella buscaba Neruda.\nSe encontraron en la sección de viajes.\n\nDesde ese momento, todo empezó a tener sentido.", 'svg_label' => 'Roma · 2021'],
            ['title' => 'La propuesta',              'subtitle' => 'Mérida, 2024',          'body' => "Creyó que iban a cenar.\nEn realidad, toda la familia los esperaba en el jardín de los abuelos.\nCon la centenaria ceiba de testigo, preguntó.\n\nLa respuesta fue, por supuesto, sí.", 'svg_label' => 'Mérida · 2024'],
        ]);

        $this->seedRomanticPhrases($wedding);

        return $wedding;
    }

    // ────────────────────────────────────────────────────────────────
    // EVENTO 2 — XV Valeria — schedule/story/dress_code OFF — sweet16
    // ────────────────────────────────────────────────────────────────

    private function seedXv(): Event
    {
        $eventDate = Carbon::create(2026, 5, 15);

        $attrs = [
            'type'       => 'xv',
            'name'       => 'XV Años de Valeria',
            'slug'       => 'xv-valeria',
            'status'     => Event::STATUS_ACTIVE,
            'event_date' => $eventDate,
            'start_time' => '17:00:00',
            'end_time'   => '01:00:00',
            'plan_key'   => Event::PLAN_PREMIUM,
            'owner_name'  => 'Familia Guerrero',
            'owner_email' => 'valeria.xv@camaleon.test',
            'auto_cleanup_after_days' => 90,

            'modules' => [
                'countdown'              => true,
                'story'                  => false,   // OFF
                'schedule'               => false,   // OFF
                'dress_code'             => false,   // OFF
                'rsvp'                   => true,
                'gifts'                  => true,
                'songs'                  => true,
                'gallery'                => true,
                'guest_photos_upload'    => true,
                'romantic_phrases'       => false,   // OFF
                'public_attendance_list' => true,
                'map'                    => true,
            ],

            'settings' => [
                'theme_palette'                      => 'sweet16',

                'playlist_enabled'                   => true,
                'playlist_allow_guests_to_add_songs' => true,
                'playlist_max_songs_per_guest'       => 5,
                'playlist_max_votes_per_guest'       => 10,
                'public_show_song_author'            => false,

                'gifts_require_invitation_code'      => true,
                'gifts_allow_unclaim'                => false,
                'gifts_hide_purchased_from_public'   => true,
                'gifts_allow_multi_unit_reserve'     => false,
                'gifts_show_claimers_public'         => false,
                'gifts_max_units_per_guest_per_gift' => 1,

                'guest_photos_max_per_guest'         => 5,
                'guest_photos_auto_approve'          => true,

                'countdown_expired_label'            => '¡Hoy es el gran día de Valeria! 🌸',
            ],
        ];

        if (Schema::hasColumn('events', 'owner_user_id')) {
            $attrs['owner_user_id'] = $this->resolveDemoClientId();
        }

        $xv = Event::query()->updateOrCreate(['slug' => $attrs['slug']], $attrs);
        $this->purgeEventChildren($xv);

        $this->seedLocations($xv, [
            ['type' => 'ceremony',  'name' => 'Basílica de Guadalupe', 'address' => 'Plaza de las Américas 1, Villa de Guadalupe, CDMX', 'maps_url' => 'https://maps.google.com', 'display_order' => 1],
            ['type' => 'reception', 'name' => 'Salón Cristal Bosques', 'address' => 'Blvd. Manuel Ávila Camacho 88, Lomas de Chapultepec', 'maps_url' => 'https://maps.google.com', 'display_order' => 2],
        ]);

        $guests = $this->seedGuests($xv, [
            ['name' => 'Camila Reyes Fuentes',    'email' => 'camila.r@demo.mx', 'invitation_code' => 'VALE0001', 'invited_seats' => 2, 'rsvp_status' => Guest::RSVP_PENDING, 'guests_confirmed' => null, 'show_in_public_list' => false],
            ['name' => 'Isabela Morales Vega',    'email' => 'isa.m@demo.mx',    'invitation_code' => 'VALE0002', 'invited_seats' => 1, 'rsvp_status' => Guest::RSVP_YES,     'guests_confirmed' => 1,    'show_in_public_list' => true,  'rsvp_message' => '¡Ya quiero bailar! 💃'],
            ['name' => 'Santiago Gutiérrez Nava', 'email' => 'santi.g@demo.mx',  'invitation_code' => 'VALE0003', 'invited_seats' => 2, 'rsvp_status' => Guest::RSVP_YES,     'guests_confirmed' => 2,    'show_in_public_list' => true,  'rsvp_message' => 'Con mi mamá, ahí estamos 🎉'],
            ['name' => 'Daniela Ortiz Blanco',    'email' => 'dani.o@demo.mx',   'invitation_code' => 'VALE0004', 'invited_seats' => 1, 'rsvp_status' => Guest::RSVP_NO,      'guests_confirmed' => 0,    'show_in_public_list' => false, 'rsvp_message' => 'Estaré de intercambio, lo siento mucho.'],
        ]);

        // Itinerario existe en DB pero módulo está OFF (no se muestra en la vista pública)
        $this->seedSchedules($xv, $eventDate, [
            ['title' => 'Misa de acción de gracias', 'description' => 'Ceremonia religiosa familiar.',               'starts_at' => $eventDate->copy()->setTime(15, 0), 'ends_at' => $eventDate->copy()->setTime(16, 30), 'location_label' => 'Basílica de Guadalupe',  'display_order' => 1],
            ['title' => 'Recepción & cóctel',        'description' => 'Bienvenida y aperitivos.',                   'starts_at' => $eventDate->copy()->setTime(17, 0), 'ends_at' => $eventDate->copy()->setTime(18, 30), 'location_label' => 'Salón Cristal Bosques', 'display_order' => 2],
            ['title' => 'Cena & vals',                'description' => 'Cena de gala y vals de presentación.',      'starts_at' => $eventDate->copy()->setTime(19, 0), 'ends_at' => $eventDate->copy()->setTime(21, 0),  'location_label' => 'Salón Cristal Bosques', 'display_order' => 3],
            ['title' => 'Fiesta',                     'description' => 'DJ y pista abierta hasta la medianoche.',   'starts_at' => $eventDate->copy()->setTime(21, 0), 'ends_at' => $eventDate->copy()->addDay()->setTime(1, 0), 'location_label' => 'Salón Cristal Bosques', 'display_order' => 4],
        ]);

        $songs = $this->seedSongs($xv, [
            ['title' => 'Flowers',       'artist' => 'Miley Cyrus',          'message_for_couple' => 'Para Valeria, la más chingona. 🌸',    'suggested_by_guest' => 'VALE0002', 'show_author' => false, 'status' => 'approved'],
            ['title' => 'Blinding Lights','artist' => 'The Weeknd',           'message_for_couple' => 'Para cuando ya queramos bailar todo.', 'suggested_by_guest' => 'VALE0003', 'show_author' => false, 'status' => 'approved'],
            ['title' => 'Cupid',         'artist' => 'FIFTY FIFTY',           'message_for_couple' => 'La canción del año obvio 😭',          'suggested_by_guest' => 'VALE0001', 'show_author' => false, 'status' => 'approved'],
            ['title' => 'Dance The Night','artist' => 'Dua Lipa',             'message_for_couple' => 'Para el momento más emocionante.',     'suggested_by_guest' => 'VALE0002', 'show_author' => false, 'status' => 'approved'],
        ], $guests);

        if (isset($songs['Flowers'])) {
            $this->seedSongVotes($xv, $songs['Flowers'], [['guest_code' => 'VALE0002', 'qty' => 1], ['guest_code' => 'VALE0003', 'qty' => 1]], $guests);
        }

        $this->seedPhotos($xv, [
            ['type' => 'hero',         'file_path' => "events/{$xv->id}/photos/originals/hero.jpg",      'image_url' => 'https://picsum.photos/seed/pink-ballroom-xv/1200/700',  'caption' => null,                        'display_order' => 1, 'guest_code' => null,        'svg_title' => 'XV Años de Valeria', 'svg_subtitle' => '15 · Mayo · 2026',   'svg_palette' => 'sweet16'],
            ['type' => 'gallery',      'file_path' => "events/{$xv->id}/photos/originals/gal-1.jpg",    'image_url' => 'https://picsum.photos/seed/pink-flowers-bokeh/800/600',  'caption' => 'Sesión con el vestido rosa','display_order' => 1, 'guest_code' => null,        'svg_title' => 'El vestido',         'svg_subtitle' => 'Sesión de fotos',    'svg_palette' => 'sweet16'],
            ['type' => 'gallery',      'file_path' => "events/{$xv->id}/photos/originals/gal-2.jpg",    'image_url' => 'https://picsum.photos/seed/family-evening-warm/800/600', 'caption' => 'Con toda mi familia',       'display_order' => 2, 'guest_code' => null,        'svg_title' => 'Familia',            'svg_subtitle' => 'Los Guerrero',       'svg_palette' => 'sweet16'],
            ['type' => 'gallery',      'file_path' => "events/{$xv->id}/photos/originals/gal-3.jpg",    'image_url' => 'https://picsum.photos/seed/friends-celebration/800/600', 'caption' => 'Mis mejores amigas',        'display_order' => 3, 'guest_code' => null,        'svg_title' => 'Amigas',             'svg_subtitle' => 'Las de siempre',     'svg_palette' => 'sweet16'],
            ['type' => 'guest_upload', 'file_path' => "events/{$xv->id}/guest-photos/originals/g1.jpg", 'image_url' => 'https://picsum.photos/seed/party-entrance-lights/600/600','caption' => 'Llegando al salón ✨',      'display_order' => 1, 'guest_code' => 'VALE0002',  'svg_title' => 'Foto Invitada',      'svg_subtitle' => 'Isabela · Llegada',  'svg_palette' => 'sweet16'],
        ], $guests);

        $gifts = $this->seedGifts($xv, [
            ['name' => 'AirPods Pro 2da gen',      'description' => 'AirPods Pro con estuche MagSafe. El regalo más pedido.',            'store_label' => 'Apple Store', 'url' => null, 'quantity' => 1, 'display_order' => 1],
            ['name' => 'Cámara instax FUJIFILM',   'description' => 'Cámara de fotos instantáneas con 2 rollos de película incluidos.', 'store_label' => 'Liverpool',   'url' => null, 'quantity' => 1, 'display_order' => 2],
            ['name' => 'Gift card Amazon $1,000',  'description' => 'Tarjeta de regalo canjeable en Amazon México.',                    'store_label' => 'Amazon',      'url' => null, 'quantity' => 5, 'display_order' => 3],
            ['name' => 'Sobre regalo (monto libre)','description' => 'Cualquier aportación con mucho cariño 💕',                       'store_label' => null,          'url' => null, 'quantity' => 99,'display_order' => 4],
        ]);

        if (isset($gifts['Cámara instax FUJIFILM'], $guests['VALE0002'])) {
            $this->seedGiftClaim($xv, $gifts['Cámara instax FUJIFILM'], $guests['VALE0002'], 1, 'purchased');
            $this->syncGiftFromClaims($gifts['Cámara instax FUJIFILM']);
        }

        // Dress codes existen en DB pero módulo OFF
        $this->seedDressCodesDetailed($xv, [
            ['title' => 'Damas',      'description' => 'Vestido de gala en tonos rosa, nude o lavanda. No blanco ni rojo.',            'icon' => 'dress', 'display_order' => 1],
            ['title' => 'Caballeros', 'description' => 'Traje formal. Colores claros de temporada primavera.',                         'icon' => 'suit',  'display_order' => 2],
        ]);

        $this->seedStoriesDetailed($xv, [
            ['title' => 'Sobre Valeria', 'subtitle' => 'Mis quince', 'body' => "Fifteen años de risas, aventuras y crecer.\nGracias a todos los que forman parte de este camino.", 'svg_label' => 'Valeria · 15'],
        ]);

        return $xv;
    }

    // ────────────────────────────────────────────────────────────────
    // EVENTO 3 — Cena Corporativa Grupo El Roble — songs/gallery/countdown/story/dress_code OFF — corp
    // ────────────────────────────────────────────────────────────────

    private function seedCorp(): Event
    {
        $eventDate = Carbon::create(2026, 6, 20);

        $attrs = [
            'type'       => 'corporate',
            'name'       => 'Cena Anual Grupo El Roble',
            'slug'       => 'cena-grupo-el-roble',
            'status'     => Event::STATUS_ACTIVE,
            'event_date' => $eventDate,
            'start_time' => '19:00:00',
            'end_time'   => '23:00:00',
            'plan_key'   => Event::PLAN_PREMIUM,
            'owner_name'  => 'Grupo El Roble',
            'owner_email' => 'eventos@grupoelroble.camaleon.test',
            'auto_cleanup_after_days' => 30,

            'modules' => [
                'countdown'              => false,  // OFF
                'story'                  => false,  // OFF
                'schedule'               => true,
                'dress_code'             => false,  // OFF
                'rsvp'                   => true,
                'gifts'                  => true,
                'songs'                  => false,  // OFF
                'gallery'                => false,  // OFF
                'guest_photos_upload'    => false,  // OFF
                'romantic_phrases'       => false,  // OFF
                'public_attendance_list' => true,
                'map'                    => true,
            ],

            'settings' => [
                'theme_palette' => 'corp',

                'gifts_require_invitation_code'      => false,
                'gifts_allow_unclaim'                => false,
                'gifts_hide_purchased_from_public'   => false,
                'gifts_allow_multi_unit_reserve'     => false,
                'gifts_show_claimers_public'         => false,
                'gifts_max_units_per_guest_per_gift' => 1,
            ],
        ];

        if (Schema::hasColumn('events', 'owner_user_id')) {
            $attrs['owner_user_id'] = $this->resolveDemoClientId();
        }

        $corp = Event::query()->updateOrCreate(['slug' => $attrs['slug']], $attrs);
        $this->purgeEventChildren($corp);

        $this->seedLocations($corp, [
            ['type' => 'reception', 'name' => 'Centro de Convenciones Presidente', 'address' => 'Campo Elíseos 218, Polanco, CDMX', 'maps_url' => 'https://maps.google.com', 'display_order' => 1],
            ['type' => 'other',     'name' => 'Valet parking',                      'address' => 'Acceso Av. Campos Elíseos',          'maps_url' => null,                      'display_order' => 2],
        ]);

        $guests = $this->seedGuests($corp, [
            ['name' => 'Lic. Carmen Espinoza Ruiz',   'email' => 'c.espinoza@demo.mx', 'invitation_code' => 'CORP0001', 'invited_seats' => 1, 'rsvp_status' => Guest::RSVP_YES,     'guests_confirmed' => 1, 'show_in_public_list' => true,  'rsvp_message' => 'Confirmo asistencia. ¡Hasta el viernes!'],
            ['name' => 'Ing. Marco Torres Altamirano','email' => 'm.torres@demo.mx',   'invitation_code' => 'CORP0002', 'invited_seats' => 1, 'rsvp_status' => Guest::RSVP_YES,     'guests_confirmed' => 1, 'show_in_public_list' => true,  'rsvp_message' => 'Asistencia confirmada.'],
            ['name' => 'Dra. Patricia Vidal Soto',    'email' => 'p.vidal@demo.mx',    'invitation_code' => 'CORP0003', 'invited_seats' => 1, 'rsvp_status' => Guest::RSVP_PENDING, 'guests_confirmed' => null,'show_in_public_list' => false],
            ['name' => 'C.P. Héctor Ramírez León',    'email' => 'h.ramirez@demo.mx',  'invitation_code' => 'CORP0004', 'invited_seats' => 1, 'rsvp_status' => Guest::RSVP_NO,      'guests_confirmed' => 0, 'show_in_public_list' => false, 'rsvp_message' => 'Conflicto de agenda. Disculpe las molestias.'],
        ]);

        $this->seedSchedules($corp, $eventDate, [
            ['title' => 'Registro y bienvenida',    'description' => 'Acreditación, gafete y cocktail de bienvenida.',                                  'starts_at' => $eventDate->copy()->setTime(19, 0),  'ends_at' => $eventDate->copy()->setTime(19, 45), 'location_label' => 'Lobby principal',    'display_order' => 1],
            ['title' => 'Palabras del Director',    'description' => 'Mensaje anual del Director General, Lic. Ernesto Garza Montoya.',                 'starts_at' => $eventDate->copy()->setTime(19, 45), 'ends_at' => $eventDate->copy()->setTime(20, 15), 'location_label' => 'Auditorio A',        'display_order' => 2],
            ['title' => 'Entrega de reconocimientos','description' => 'Reconocimiento a colaboradores destacados del año 2025–2026.',                   'starts_at' => $eventDate->copy()->setTime(20, 15), 'ends_at' => $eventDate->copy()->setTime(20, 45), 'location_label' => 'Auditorio A',        'display_order' => 3],
            ['title' => 'Cena de gala',             'description' => 'Menú de tres tiempos. Menú vegano disponible bajo solicitud previa.',             'starts_at' => $eventDate->copy()->setTime(21, 0),  'ends_at' => $eventDate->copy()->setTime(22, 15), 'location_label' => 'Salón Roble',        'display_order' => 4],
            ['title' => 'Networking & cierre',      'description' => 'Espacio para convivencia y clausura del evento.',                                 'starts_at' => $eventDate->copy()->setTime(22, 15), 'ends_at' => $eventDate->copy()->setTime(23, 0),  'location_label' => 'Terraza Ejecutiva',  'display_order' => 5],
        ]);

        // Sin fotos de galería (módulo OFF), solo hero
        $this->seedPhotos($corp, [
            ['type' => 'hero', 'file_path' => "events/{$corp->id}/photos/originals/hero.jpg", 'image_url' => 'https://picsum.photos/seed/corporate-gala-venue/1200/700', 'caption' => null, 'display_order' => 1, 'guest_code' => null, 'svg_title' => 'Cena Anual 2026', 'svg_subtitle' => 'Grupo El Roble · CDMX', 'svg_palette' => 'corp'],
        ], $guests);

        // Gifts = reconocimientos/sorteos
        $gifts = $this->seedGifts($corp, [
            ['name' => 'Reconocimiento "Colaborador del Año"', 'description' => 'Premio al colaborador con mayor desempeño anual. Incluye placa y bono.', 'store_label' => 'RH Grupo El Roble', 'url' => null, 'quantity' => 1, 'display_order' => 1],
            ['name' => 'Premio Innovación 2026',               'description' => 'Para el equipo con el proyecto más innovador del año.',                   'store_label' => 'RH Grupo El Roble', 'url' => null, 'quantity' => 1, 'display_order' => 2],
            ['name' => 'Sorteo: viaje a Cancún (2 personas)',  'description' => 'Boleto de avión + 3 noches en hotel. Se rifa durante la cena.',          'store_label' => null,                'url' => null, 'quantity' => 1, 'display_order' => 3],
        ]);

        if (isset($gifts['Reconocimiento "Colaborador del Año"'], $guests['CORP0001'])) {
            $this->seedGiftClaim($corp, $gifts['Reconocimiento "Colaborador del Año"'], $guests['CORP0001'], 1, 'reserved');
            $this->syncGiftFromClaims($gifts['Reconocimiento "Colaborador del Año"']);
        }

        return $corp;
    }

    // ────────────────────────────────────────────────────────────────
    // Helpers
    // ────────────────────────────────────────────────────────────────

    private function resolveDemoClientId(): ?int
    {
        if ($this->demoClientId !== null) {
            return $this->demoClientId;
        }
        if (!Schema::hasTable('users')) {
            return null;
        }
        $this->demoClientId = User::query()->where('email', 'client.demo@camaleon.test')->value('id');
        return $this->demoClientId;
    }

    private function purgeEventChildren(Event $event): void
    {
        if (!Schema::hasTable('events')) return;

        // Hard delete via DB::table para evitar problemas con soft deletes
        // y constraints unique en re-ejecuciones del seeder.
        $tables = [
            'event_gift_claims',
            'event_gifts',
            'song_votes',
            'event_songs',
            'event_stories',
            'event_dress_codes',
            'event_romantic_phrases',
            'event_schedules',
            'event_locations',
            'event_photos',
            'guests',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->where('event_id', $event->id)->delete();
            }
        }
    }

    private function seedLocations(Event $event, array $locations): void
    {
        if (!Schema::hasTable('event_locations')) return;
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

    private function seedGuests(Event $event, array $guests): array
    {
        if (!Schema::hasTable('guests')) return [];
        $map = [];
        foreach ($guests as $g) {
            $guest = Guest::create([
                'event_id'            => $event->id,
                'name'                => $g['name'],
                'email'               => $g['email'] ?? null,
                'invitation_code'     => $g['invitation_code'],
                'invited_seats'       => (int) ($g['invited_seats'] ?? 1),
                'rsvp_status'         => $g['rsvp_status'] ?? Guest::RSVP_PENDING,
                'rsvp_message'        => $g['rsvp_message'] ?? null,
                'rsvp_public'         => (bool) ($g['rsvp_public'] ?? false),
                'guests_confirmed'    => $g['guests_confirmed'] ?? null,
                'show_in_public_list' => (bool) ($g['show_in_public_list'] ?? false),
            ]);
            $map[$guest->invitation_code] = $guest;
        }
        return $map;
    }

    private function seedSchedules(Event $event, Carbon $eventDate, array $items): void
    {
        if (!Schema::hasTable('event_schedules')) return;
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

    private function seedSongs(Event $event, array $songs, array $guestsByCode = []): array
    {
        if (!Schema::hasTable('event_songs')) return [];
        $created = [];
        foreach ($songs as $s) {
            $suggestedById = null;
            if (!empty($s['suggested_by_guest']) && isset($guestsByCode[$s['suggested_by_guest']])) {
                $suggestedById = $guestsByCode[$s['suggested_by_guest']]->id;
            }
            $song = EventSong::create([
                'event_id'              => $event->id,
                'title'                 => $s['title'],
                'artist'                => $s['artist'] ?? null,
                'url'                   => $s['url'] ?? null,
                'message_for_couple'    => $s['message_for_couple'] ?? null,
                'suggested_by_guest_id' => $suggestedById,
                'show_author'           => (bool) ($s['show_author'] ?? false),
                'status'                => $s['status'] ?? 'pending',
                'votes_count'           => 0,
            ]);
            $created[$song->title] = $song;
        }
        return $created;
    }

    private function seedSongVotes(Event $event, EventSong $song, array $votes, array $guestsByCode): void
    {
        if (!Schema::hasTable('song_votes')) return;
        $count = 0;
        foreach ($votes as $v) {
            if (!isset($guestsByCode[$v['guest_code']])) continue;
            $guest = $guestsByCode[$v['guest_code']];
            for ($i = 0; $i < (int) ($v['qty'] ?? 1); $i++) {
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

    private function seedPhotos(Event $event, array $photos, array $guestsByCode = []): void
    {
        if (!Schema::hasTable('event_photos')) return;
        foreach ($photos as $p) {
            $guestId = null;
            if (!empty($p['guest_code']) && isset($guestsByCode[$p['guest_code']])) {
                $guestId = $guestsByCode[$p['guest_code']]->id;
            }
            if (!empty($p['file_path'])) {
                $content = isset($p['image_url'])
                    ? $this->fetchOrSvg($p['image_url'], $p['svg_title'] ?? '', $p['svg_subtitle'] ?? '', $p['svg_palette'] ?? 'tuscan')
                    : $this->buildSvg($p['svg_title'] ?? '', $p['svg_subtitle'] ?? '', $p['svg_palette'] ?? 'tuscan');
                Storage::disk('public')->put($p['file_path'], $content);
            }
            EventPhoto::create([
                'event_id'       => $event->id,
                'guest_id'       => $guestId,
                'type'           => $p['type'],
                'file_path'      => $p['file_path'],
                'thumbnail_path' => $p['thumbnail_path'] ?? null,
                'caption'        => $p['caption'] ?? null,
                'status'         => 'approved',
                'display_order'  => (int) ($p['display_order'] ?? 0),
            ]);
        }
    }

    private function fetchOrSvg(string $imageUrl, string $title, string $subtitle, string $palette): string
    {
        try {
            $ctx = stream_context_create([
                'http' => [
                    'timeout'    => 12,
                    'user_agent' => 'Mozilla/5.0 CamaleonSeeder/1.0',
                ],
                'ssl' => [
                    'verify_peer'      => false,
                    'verify_peer_name' => false,
                ],
            ]);
            $bytes = @file_get_contents($imageUrl, false, $ctx);
            if ($bytes !== false && strlen($bytes) > 5_000) {
                return $bytes;
            }
        } catch (\Throwable) {}
        return $this->buildSvg($title, $subtitle, $palette);
    }

    private function seedGifts(Event $event, array $gifts): array
    {
        if (!Schema::hasTable('event_gifts')) return [];
        $created = [];
        foreach ($gifts as $g) {
            $gift = EventGift::create([
                'event_id'          => $event->id,
                'name'              => $g['name'],
                'description'       => $g['description'] ?? null,
                'store_label'       => $g['store_label'] ?? null,
                'url'               => $g['url'] ?? null,
                'quantity'          => (int) ($g['quantity'] ?? 1),
                'quantity_reserved' => 0,
                'status'            => defined(EventGift::class . '::STATUS_PENDING') ? EventGift::STATUS_PENDING : 'pending',
                'display_order'     => (int) ($g['display_order'] ?? 0),
            ]);
            $created[$gift->name] = $gift;
        }
        return $created;
    }

    private function seedGiftClaim(Event $event, EventGift $gift, Guest $guest, int $quantity, string $status): void
    {
        if (!Schema::hasTable('event_gift_claims')) return;
        EventGiftClaim::create([
            'event_id' => $event->id,
            'gift_id'  => $gift->id,
            'guest_id' => $guest->id,
            'quantity' => max(1, $quantity),
            'status'   => $status,
        ]);
    }

    private function syncGiftFromClaims(EventGift $gift): void
    {
        if (!Schema::hasTable('event_gift_claims')) return;

        $reservedQty = (int) EventGiftClaim::query()
            ->where('gift_id', $gift->id)
            ->whereIn('status', ['reserved', 'purchased'])
            ->sum('quantity');

        $gift->quantity_reserved = max(0, $reservedQty);

        $hasPurchased = EventGiftClaim::query()->where('gift_id', $gift->id)->where('status', 'purchased')->exists();

        $gift->status = $hasPurchased ? 'purchased' : ($gift->quantity_reserved > 0 ? 'reserved' : 'pending');
        if ($hasPurchased) $gift->purchased_at = now();
        elseif ($gift->quantity_reserved > 0) $gift->reserved_at = now();

        $gift->save();
    }

    private function seedDressCodesDetailed(Event $event, array $codes): void
    {
        if (!class_exists(EventDressCode::class) || !Schema::hasTable('event_dress_codes')) return;
        foreach ($codes as $c) {
            $photo = $this->createSvgPhoto($event, "dress-code/{$c['icon']}", $c['title'], 'Código de vestimenta', 'dress_code', $c['display_order']);
            EventDressCode::create([
                'event_id'         => $event->id,
                'title'            => $c['title'],
                'description'      => $c['description'],
                'icon'             => $c['icon'],
                'example_photo_id' => $photo?->id,
                'display_order'    => $c['display_order'],
                'is_enabled'       => true,
            ]);
        }
    }

    private function seedStoriesDetailed(Event $event, array $stories): void
    {
        if (!Schema::hasTable('event_stories')) return;
        foreach ($stories as $i => $s) {
            $photo = $this->createSvgPhoto($event, "story/cap-{$i}", $s['svg_label'] ?? $s['title'], $s['subtitle'] ?? '', 'story', $i + 1);
            EventStory::create([
                'event_id'         => $event->id,
                'title'            => $s['title'],
                'subtitle'         => $s['subtitle'] ?? null,
                'body'             => $s['body'] ?? null,
                'example_photo_id' => $photo?->id,
                'display_order'    => $i + 1,
                'is_enabled'       => true,
            ]);
        }
    }

    private function seedRomanticPhrases(Event $event): void
    {
        if (!class_exists(EventRomanticPhrase::class) || !Schema::hasTable('event_romantic_phrases')) return;
        $phrases = [
            ['phrase' => 'El amor no se mira con los ojos, sino con el alma.',                        'author' => 'William Shakespeare'],
            ['phrase' => 'Donde hay amor, hay vida.',                                                  'author' => 'Mahatma Gandhi'],
            ['phrase' => 'Amar no es mirarse el uno al otro; es mirar juntos en la misma dirección.', 'author' => 'Antoine de Saint-Exupéry'],
        ];
        foreach ($phrases as $i => $p) {
            EventRomanticPhrase::create([
                'event_id'      => $event->id,
                'phrase'        => $p['phrase'],
                'author'        => $p['author'],
                'display_order' => $i + 1,
                'is_enabled'    => true,
            ]);
        }
    }

    private function createSvgPhoto(Event $event, string $slug, string $title, string $subtitle, string $type, int $order): ?EventPhoto
    {
        if (!Schema::hasTable('event_photos')) return null;
        $palette = data_get($event->settings, 'theme_palette', 'tuscan');
        $path = "events/{$event->id}/{$slug}.svg";
        Storage::disk('public')->put($path, $this->buildSvg($title, $subtitle, $palette));
        return EventPhoto::create([
            'event_id'       => $event->id,
            'guest_id'       => null,
            'type'           => $type,
            'file_path'      => $path,
            'thumbnail_path' => null,
            'caption'        => $title,
            'status'         => 'approved',
            'display_order'  => $order,
        ]);
    }

    // SVGs con colores de cada paleta para hacer la preview visualmente distinta
    private function buildSvg(string $title, string $subtitle, string $palette = 'tuscan'): string
    {
        $t = htmlspecialchars($title,    ENT_QUOTES, 'UTF-8');
        $s = htmlspecialchars($subtitle, ENT_QUOTES, 'UTF-8');

        $themes = [
            'tuscan'  => ['bg1' => '#2A241D', 'bg2' => '#5C5247', 'accent' => '#B08A5B', 'text' => '#FAF9F6'],
            'coastal' => ['bg1' => '#1F2B2A', 'bg2' => '#4D5C5A', 'accent' => '#6E9889', 'text' => '#F7F8F6'],
            'sweet16' => ['bg1' => '#3A1F26', 'bg2' => '#6B4750', 'accent' => '#D87C7E', 'text' => '#FFF7F4'],
            'noche'   => ['bg1' => '#15131A', 'bg2' => '#26222C', 'accent' => '#E5B568', 'text' => '#F4EFE2'],
            'corp'    => ['bg1' => '#11151D', 'bg2' => '#3F4754', 'accent' => '#2F4858', 'text' => '#F5F6F8'],
        ];

        $c = $themes[$palette] ?? $themes['tuscan'];

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="700" viewBox="0 0 1200 700">
  <defs>
    <linearGradient id="g" x1="0" x2="0.6" y1="0" y2="1">
      <stop offset="0" stop-color="{$c['bg1']}"/>
      <stop offset="1" stop-color="{$c['bg2']}"/>
    </linearGradient>
  </defs>
  <rect width="1200" height="700" fill="url(#g)"/>
  <rect x="0" y="0" width="6" height="700" fill="{$c['accent']}"/>
  <text x="80" y="340" font-family="Georgia, serif" font-size="72" font-weight="400" fill="{$c['text']}" font-style="italic">{$t}</text>
  <text x="80" y="400" font-family="Arial, sans-serif" font-size="26" font-weight="300" fill="{$c['accent']}" letter-spacing="4">{$s}</text>
</svg>
SVG;
    }
}
