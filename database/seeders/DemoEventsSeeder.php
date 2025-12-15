<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventGift;
use App\Models\EventLocation;
use App\Models\EventPhoto;
use App\Models\EventSong;
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
                'guest_photos_upload'    => true,
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

                // Configuración de regalos
                'gifts_require_invitation_code'      => true,
                'gifts_allow_unclaim'                => true,
                'gifts_allow_show_buyer_name'        => true,
                'gifts_hide_purchased_from_public'   => false,
                'gifts_max_units_per_guest_per_gift' => 1,
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

        // Invitado demo para probar RSVP, playlist y regalos
        $weddingGuest = Guest::create([
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

        // 📸 Fotos demo para la galería de la boda
        EventPhoto::create([
            'event_id'       => $wedding->id,
            'type'           => EventPhoto::TYPE_GALLERY,
            'file_path'      => 'events/'.$wedding->id.'/photos/originals/boda-1.jpg',
            'thumbnail_path' => 'events/'.$wedding->id.'/photos/thumbnails/boda-1_thumb.jpg',
            'caption'        => 'Sesión de fotos de compromiso',
            'status'         => EventPhoto::STATUS_APPROVED,
            'display_order'  => 1,
        ]);

        EventPhoto::create([
            'event_id'       => $wedding->id,
            'type'           => EventPhoto::TYPE_GALLERY,
            'file_path'      => 'events/'.$wedding->id.'/photos/originals/boda-2.jpg',
            'thumbnail_path' => 'events/'.$wedding->id.'/photos/thumbnails/boda-2_thumb.jpg',
            'caption'        => 'Momento especial durante la ceremonia',
            'status'         => EventPhoto::STATUS_APPROVED,
            'display_order'  => 2,
        ]);

        EventPhoto::create([
            'event_id'       => $wedding->id,
            'type'           => EventPhoto::TYPE_GALLERY,
            'file_path'      => 'events/'.$wedding->id.'/photos/originals/boda-3.jpg',
            'thumbnail_path' => 'events/'.$wedding->id.'/photos/thumbnails/boda-3_thumb.jpg',
            'caption'        => 'Fiesta con amigos y familiares',
            'status'         => EventPhoto::STATUS_APPROVED,
            'display_order'  => 3,
        ]);

        // 🎁 Regalos demo para la boda
        EventGift::create([
            'event_id'          => $wedding->id,
            'name'              => 'Juego de vasos de cristal',
            'description'       => 'Set de 6 vasos de cristal para bebidas.',
            'store_label'       => 'Liverpool',
            'url'               => 'https://example.com/regalo-vasos',
            'quantity'          => 1,
            'quantity_reserved' => 0,
            'status'            => EventGift::STATUS_PENDING,
            'display_order'     => 1,
        ]);

        EventGift::create([
            'event_id'          => $wedding->id,
            'name'              => 'Juego de platos x2',
            'description'       => 'Juegos de platos llanos y hondos para 4 personas.',
            'store_label'       => 'Amazon',
            'url'               => 'https://example.com/regalo-platos',
            'quantity'          => 2,
            'quantity_reserved' => 1, // simulamos que ya hay uno apartado
            'status'            => EventGift::STATUS_RESERVED,
            'display_order'     => 2,
        ]);

        EventGift::create([
            'event_id'          => $wedding->id,
            'name'              => 'Tostador eléctrico',
            'description'       => 'Tostador para 4 rebanadas.',
            'store_label'       => 'Coppel',
            'url'               => 'https://example.com/regalo-tostador',
            'quantity'          => 1,
            'quantity_reserved' => 1,
            'status'            => EventGift::STATUS_PURCHASED,
            'display_order'     => 3,
        ]);

        // 2) Evento XV de ejemplo
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
                'gifts'                  => true,
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

                'gifts_require_invitation_code'      => true,
                'gifts_allow_unclaim'                => false,
                'gifts_allow_show_buyer_name'        => false,
                'gifts_hide_purchased_from_public'   => true, // aquí ocultaremos purchased
                'gifts_max_units_per_guest_per_gift' => 1,
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

        // 📸 Fotos demo para la galería de los XV
        EventPhoto::create([
            'event_id'       => $xv->id,
            'type'           => EventPhoto::TYPE_GALLERY,
            'file_path'      => 'events/'.$xv->id.'/photos/originals/xv-1.jpg',
            'thumbnail_path' => 'events/'.$xv->id.'/photos/thumbnails/xv-1_thumb.jpg',
            'caption'        => 'Sesión de fotos con el vestido',
            'status'         => EventPhoto::STATUS_APPROVED,
            'display_order'  => 1,
        ]);

        EventPhoto::create([
            'event_id'       => $xv->id,
            'type'           => EventPhoto::TYPE_GALLERY,
            'file_path'      => 'events/'.$xv->id.'/photos/originals/xv-2.jpg',
            'thumbnail_path' => 'events/'.$xv->id.'/photos/thumbnails/xv-2_thumb.jpg',
            'caption'        => 'Entrada al salón',
            'status'         => EventPhoto::STATUS_APPROVED,
            'display_order'  => 2,
        ]);

        // 🎁 Regalos demo para los XV (uno purchased que no debe mostrarse por setting)
        EventGift::create([
            'event_id'          => $xv->id,
            'name'              => 'Marco de fotos decorativo',
            'description'       => 'Marco grande para fotos del evento.',
            'store_label'       => 'Amazon',
            'url'               => 'https://example.com/xv-marco',
            'quantity'          => 1,
            'quantity_reserved' => 0,
            'status'            => EventGift::STATUS_PENDING,
            'display_order'     => 1,
        ]);

        EventGift::create([
            'event_id'          => $xv->id,
            'name'              => 'Set de luces decorativas',
            'description'       => 'Luces tipo fairy lights.',
            'store_label'       => 'MercadoLibre',
            'url'               => 'https://example.com/xv-luces',
            'quantity'          => 1,
            'quantity_reserved' => 1,
            'status'            => EventGift::STATUS_PURCHASED,
            'display_order'     => 2,
        ]);

        $xvGuest = Guest::create([
            'event_id'            => $xv->id,
            'name'                => 'Invitado Demo XV',
            'email'               => 'invitado.xv.demo@example.com',
            'phone'               => null,
            'invitation_code'     => 'XVDEMO1234',
            'invited_seats'       => 3,
            'rsvp_status'         => Guest::RSVP_PENDING,
            'rsvp_message'        => null,
            'rsvp_public'         => true,
            'guests_confirmed'    => null,
            'show_in_public_list' => true,
            'checked_in_at'       => null,
        ]);
    }
}
