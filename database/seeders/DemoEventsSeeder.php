<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventLocation;
use App\Models\EventSong;
use App\Models\EventPhoto;
use App\Models\EventSchedule;
use App\Models\Guest;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DemoEventsSeeder extends Seeder
{
    /**
     * Ejecuta el seeder.
     */
    public function run(): void
    {
        // 1) Boda de prueba
        $wedding = Event::create([
            'type'       => 'wedding',
            'name'       => 'Boda de Prueba Ana & Luis',
            'slug'       => 'boda-prueba-ana-luis',
            'status'     => Event::STATUS_ACTIVE,
            'event_date' => Carbon::create(2025, 12, 31),
            'start_time' => '18:00:00',
            'end_time'   => '02:00:00',

            'theme_key'       => 'romantic',
            'primary_color'   => '#f472b6',
            'secondary_color' => '#0f172a',
            'accent_color'    => '#f9a8d4',
            'font_family'     => 'Playfair Display',

            'modules' => [
                'gallery'                => true,
                'playlist_suggestions'   => true,
                'playlist_votes'         => true,
                'songs'                  => true,
                'rsvp'                   => true,
                'public_attendance_list' => false,
                'dress_code'             => true,
                'gifts'                  => true,
                'guest_photos_upload'    => false,
                'romantic_phrases'       => true,
                'countdown'              => true,
                'map'                    => true,
                'schedule'               => true,
            ],
            'settings' => [
                'playlist_enabled'                   => true,
                'playlist_allow_guests_to_add_songs' => true,
                'playlist_max_songs_per_guest'       => 3,
                'playlist_max_votes_per_guest'       => 10,
                'public_show_song_author'            => true,
            ],

            'owner_name'  => 'Ana & Luis',
            'owner_email' => 'ana.y.luis@example.com',

            'auto_cleanup_after_days' => 60,
        ]);

        // Ubicaciones de la boda
        EventLocation::create([
            'event_id'      => $wedding->id,
            'type'          => 'ceremony',
            'name'          => 'Iglesia de Prueba',
            'address'       => 'Centro, Ciudad de Ejemplo',
            'maps_url'      => 'https://maps.google.com',
            'display_order' => 1,
        ]);

        EventLocation::create([
            'event_id'      => $wedding->id,
            'type'          => 'reception',
            'name'          => 'Salón Jardín de Ejemplo',
            'address'       => 'Av. Principal 123, Ciudad de Ejemplo',
            'maps_url'      => 'https://maps.google.com',
            'display_order' => 2,
        ]);

        // Invitado demo para probar RSVP y playlist
        Guest::create([
            'event_id'            => $wedding->id,
            'name'                => 'Invitado Demo',
            'email'               => 'invitado.demo@example.com',
            'phone'               => null,
            'invitation_code'     => 'DEMO1234',
            'invited_seats'       => 2,
            'rsvp_status'         => Guest::RSVP_PENDING,
            'rsvp_message'        => null,
            'rsvp_public'         => false,
            'guests_confirmed'    => null,
            'show_in_public_list' => false,
            'checked_in_at'       => null,
        ]);

        // Canciones sugeridas para la boda (ya con votos de ejemplo)
        EventSong::create([
            'event_id'           => $wedding->id,
            'title'              => 'Perfect',
            'artist'             => 'Ed Sheeran',
            'url'                => 'https://open.spotify.com/track/0tgVpDi06FyKpA1z0VMD4v',
            'message_for_couple' => 'Para el primer baile, obvio 💕',
            'show_author'        => true,
            'status'             => EventSong::STATUS_APPROVED,
            'votes_count'        => 15,
        ]);

        EventSong::create([
            'event_id'           => $wedding->id,
            'title'              => 'Can\'t Help Falling in Love',
            'artist'             => 'Elvis Presley',
            'url'                => null,
            'message_for_couple' => 'Para el vals con los papás.',
            'show_author'        => true,
            'status'             => EventSong::STATUS_APPROVED,
            'votes_count'        => 9,
        ]);

        EventSong::create([
            'event_id'           => $wedding->id,
            'title'              => 'Marry You',
            'artist'             => 'Bruno Mars',
            'url'                => null,
            'message_for_couple' => 'Para levantar a todos de las mesas.',
            'show_author'        => true,
            'status'             => EventSong::STATUS_APPROVED,
            'votes_count'        => 7,
        ]);

        // 📸 Foto de portada (hero) para la boda
        EventPhoto::create([
            'event_id'       => $wedding->id,
            'type'           => EventPhoto::TYPE_HERO,
            'file_path'      => 'events/'.$wedding->id.'/photos/originals/boda-hero.jpg',
            'thumbnail_path' => null,
            'caption'        => 'Portada oficial de la boda Ana & Luis',
            'status'         => EventPhoto::STATUS_APPROVED,
            'display_order'  => 1,
        ]);

        // 📸 Fotos demo para la galería de la boda
        EventPhoto::create([
            'event_id'      => $wedding->id,
            'type'          => EventPhoto::TYPE_GALLERY,
            'file_path'     => 'events/'.$wedding->id.'/photos/originals/boda-1.jpg',
            'thumbnail_path'=> 'events/'.$wedding->id.'/photos/thumbnails/boda-1_thumb.jpg',
            'caption'       => 'Sesión de fotos de compromiso',
            'status'        => EventPhoto::STATUS_APPROVED,
            'display_order' => 1,
        ]);

        EventPhoto::create([
            'event_id'      => $wedding->id,
            'type'          => EventPhoto::TYPE_GALLERY,
            'file_path'     => 'events/'.$wedding->id.'/photos/originals/boda-2.jpg',
            'thumbnail_path'=> 'events/'.$wedding->id.'/photos/thumbnails/boda-2_thumb.jpg',
            'caption'       => 'Momento especial durante la ceremonia',
            'status'        => EventPhoto::STATUS_APPROVED,
            'display_order' => 2,
        ]);

        EventPhoto::create([
            'event_id'      => $wedding->id,
            'type'          => EventPhoto::TYPE_GALLERY,
            'file_path'     => 'events/'.$wedding->id.'/photos/originals/boda-3.jpg',
            'thumbnail_path'=> 'events/'.$wedding->id.'/photos/thumbnails/boda-3_thumb.jpg',
            'caption'       => 'Fiesta con amigos y familiares',
            'status'        => EventPhoto::STATUS_APPROVED,
            'display_order' => 3,
        ]);

        // 🕒 Itinerario demo para la boda
        EventSchedule::create([
            'event_id'       => $wedding->id,
            'title'          => 'Ceremonia religiosa',
            'description'    => 'Se ruega llegar 15 minutos antes.',
            'starts_at'      => Carbon::create(2025, 12, 31, 18, 0),
            'ends_at'        => Carbon::create(2025, 12, 31, 19, 0),
            'location_label' => 'Iglesia de Prueba',
            'location_type'  => 'ceremony',
            'display_order'  => 1,
        ]);

        EventSchedule::create([
            'event_id'       => $wedding->id,
            'title'          => 'Sesión de fotos',
            'description'    => 'Fotos con familia y amigos.',
            'starts_at'      => Carbon::create(2025, 12, 31, 19, 15),
            'ends_at'        => Carbon::create(2025, 12, 31, 20, 0),
            'location_label' => 'Jardín del salón',
            'location_type'  => 'photos',
            'display_order'  => 2,
        ]);

        EventSchedule::create([
            'event_id'       => $wedding->id,
            'title'          => 'Banquete',
            'description'    => 'Servicio de cena para todos los invitados.',
            'starts_at'      => Carbon::create(2025, 12, 31, 20, 0),
            'ends_at'        => Carbon::create(2025, 12, 31, 21, 30),
            'location_label' => 'Salón Jardín de Ejemplo',
            'location_type'  => 'reception',
            'display_order'  => 3,
        ]);

        EventSchedule::create([
            'event_id'       => $wedding->id,
            'title'          => 'Primer baile',
            'description'    => 'Baile de los novios y vals con papás.',
            'starts_at'      => Carbon::create(2025, 12, 31, 21, 45),
            'ends_at'        => Carbon::create(2025, 12, 31, 22, 30),
            'location_label' => 'Pista principal',
            'location_type'  => 'dance',
            'display_order'  => 4,
        ]);

        // 2) Evento XV de ejemplo (con schedule también)
        $xv = Event::create([
            'type'       => 'xv',
            'name'       => 'XV Años de Valeria (Demo)',
            'slug'       => 'xv-valeria-demo',
            'status'     => Event::STATUS_ACTIVE,
            'event_date' => Carbon::create(2026, 5, 15),
            'start_time' => '17:00:00',
            'end_time'   => '01:00:00',

            'theme_key'       => 'xv_modern',
            'primary_color'   => '#a855f7',
            'secondary_color' => '#020617',
            'accent_color'    => '#f97316',
            'font_family'     => 'Playfair Display',

            'modules' => [
                'gallery'                => true,
                'playlist_suggestions'   => true,
                'playlist_votes'         => true,
                'songs'                  => true,
                'rsvp'                   => true,
                'public_attendance_list' => true,
                'dress_code'             => true,
                'gifts'                  => false,
                'guest_photos_upload'    => true,
                'romantic_phrases'       => false,
                'countdown'              => true,
                'map'                    => true,
                'schedule'               => true,
            ],
            'settings' => [
                'playlist_enabled'                   => true,
                'playlist_allow_guests_to_add_songs' => true,
                'playlist_max_songs_per_guest'       => 5,
                'playlist_max_votes_per_guest'       => 15,
                'public_show_song_author'            => false,
            ],

            'owner_name'  => 'Familia Demo',
            'owner_email' => 'valeria@example.com',

            'auto_cleanup_after_days' => 90,
        ]);

        EventLocation::create([
            'event_id'      => $xv->id,
            'type'          => 'reception',
            'name'          => 'Salón de Eventos Luna',
            'address'       => 'Blvd. Ejemplo 456, Ciudad de Ejemplo',
            'maps_url'      => 'https://maps.google.com',
            'display_order' => 1,
        ]);

        // 📸 Fotos demo para la galería de los XV (sin hero)
        EventPhoto::create([
            'event_id'      => $xv->id,
            'type'          => EventPhoto::TYPE_GALLERY,
            'file_path'     => 'events/'.$xv->id.'/photos/originals/xv-1.jpg',
            'thumbnail_path'=> 'events/'.$xv->id.'/photos/thumbnails/xv-1_thumb.jpg',
            'caption'       => 'Sesión de fotos con el vestido',
            'status'        => EventPhoto::STATUS_APPROVED,
            'display_order' => 1,
        ]);

        EventPhoto::create([
            'event_id'      => $xv->id,
            'type'          => EventPhoto::TYPE_GALLERY,
            'file_path'     => 'events/'.$xv->id.'/photos/originals/xv-2.jpg',
            'thumbnail_path'=> 'events/'.$xv->id.'/photos/thumbnails/xv-2_thumb.jpg',
            'caption'       => 'Entrada al salón',
            'status'        => EventPhoto::STATUS_APPROVED,
            'display_order' => 2,
        ]);

        // 🕒 Itinerario demo para los XV
        EventSchedule::create([
            'event_id'       => $xv->id,
            'title'          => 'Recepción y bienvenida',
            'description'    => 'Bienvenida a los invitados y coctel de recepción.',
            'starts_at'      => Carbon::create(2026, 5, 15, 17, 0),
            'ends_at'        => Carbon::create(2026, 5, 15, 18, 0),
            'location_label' => 'Salón de Eventos Luna',
            'location_type'  => 'reception',
            'display_order'  => 1,
        ]);

        EventSchedule::create([
            'event_id'       => $xv->id,
            'title'          => 'Presentación de la quinceañera',
            'description'    => 'Entrada de Valeria con corte de honor.',
            'starts_at'      => Carbon::create(2026, 5, 15, 18, 0),
            'ends_at'        => Carbon::create(2026, 5, 15, 18, 30),
            'location_label' => 'Pista principal',
            'location_type'  => 'presentation',
            'display_order'  => 2,
        ]);

        EventSchedule::create([
            'event_id'       => $xv->id,
            'title'          => 'Cena',
            'description'    => 'Servicio de cena para todos los invitados.',
            'starts_at'      => Carbon::create(2026, 5, 15, 19, 0),
            'ends_at'        => Carbon::create(2026, 5, 15, 20, 30),
            'location_label' => 'Salón de Eventos Luna',
            'location_type'  => 'dinner',
            'display_order'  => 3,
        ]);

        EventSchedule::create([
            'event_id'       => $xv->id,
            'title'          => 'Vals y protocolo',
            'description'    => 'Vals con papás y chambelanes, protocolo especial.',
            'starts_at'      => Carbon::create(2026, 5, 15, 21, 0),
            'ends_at'        => Carbon::create(2026, 5, 15, 21, 45),
            'location_label' => 'Pista principal',
            'location_type'  => 'dance',
            'display_order'  => 4,
        ]);
    }
}
