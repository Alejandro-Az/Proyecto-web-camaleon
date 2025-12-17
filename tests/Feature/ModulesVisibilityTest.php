<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventLocation;
use App\Models\EventDressCode;
use App\Models\EventRomanticPhrase;
use App\Models\Guest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModulesVisibilityTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function pagina_publica_muestra_countdown_y_codigo_de_vestimenta_cuando_hay_data_y_modulos_activados()
    {
        $event = Event::factory()->create([
            'status'     => Event::STATUS_ACTIVE,
            'event_date' => now()->addDays(10)->startOfDay(),
            'start_time' => '18:00:00',
            'modules'    => [
                'countdown'        => true,
                'dress_code'       => true,
                'romantic_phrases' => true,
            ],
            'settings'   => [
                'romantic_phrases_random' => false,
                'romantic_phrases_limit'  => 8,
            ],
        ]);

        EventDressCode::create([
            'event_id'      => $event->id,
            'title'         => 'Formal',
            'description'   => 'Traje y vestido.',
            'examples'      => 'Traje oscuro / vestido largo',
            'notes'         => null,
            'icon'          => null,
            'display_order' => 1,
            'is_enabled'    => true,
        ]);

        EventRomanticPhrase::create([
            'event_id'      => $event->id,
            'phrase'        => 'Y de repente, todas las canciones de amor tenían sentido.',
            'author'        => null,
            'display_order' => 1,
            'is_enabled'    => true,
        ]);

        $response = $this->get('/eventos/' . $event->slug);

        $response->assertStatus(200);
        $response->assertSee('Cuenta regresiva');
        $response->assertSee('data-countdown-target=', false);
        $response->assertSee('Código de vestimenta');
        $response->assertSee('Frases del evento');
    }

    /** @test */
    public function ubicacion_no_se_muestra_si_el_modulo_map_esta_desactivado()
    {
        $event = Event::factory()->create([
            'status' => Event::STATUS_ACTIVE,
            'modules' => [
                'map' => false,
            ],
        ]);

        EventLocation::create([
            'event_id'      => $event->id,
            'type'          => 'reception',
            'name'          => 'Lugar Test',
            'address'       => 'Calle 123',
            'maps_url'      => 'https://maps.google.com',
            'display_order' => 1,
        ]);

        $response = $this->get('/eventos/' . $event->slug);

        $response->assertStatus(200);
        $response->assertDontSee('Abrir en Google Maps');
    }

    /** @test */
    public function no_permite_sugerir_canciones_si_el_modulo_songs_esta_desactivado()
    {
        $event = Event::factory()->create([
            'status' => Event::STATUS_ACTIVE,
            'modules' => [
                'songs' => false,
            ],
        ]);

        $guest = Guest::factory()->create([
            'event_id'        => $event->id,
            'invitation_code' => 'CODE123',
        ]);

        $response = $this->postJson(route('events.songs.store', ['slug' => $event->slug]), [
            'invitation_code' => $guest->invitation_code,
            'title'           => 'Canción X',
            'artist'          => 'Artista X',
        ]);

        $response->assertStatus(404);
    }

    /** @test */
    public function no_permite_rsvp_si_el_modulo_rsvp_esta_desactivado()
    {
        $event = Event::factory()->create([
            'status' => Event::STATUS_ACTIVE,
            'modules' => [
                'rsvp' => false,
            ],
        ]);

        $guest = Guest::factory()->create([
            'event_id'        => $event->id,
            'invitation_code' => 'RSVP123',
        ]);

        $response = $this->post('/eventos/' . $event->slug . '/rsvp', [
            'invitation_code'  => $guest->invitation_code,
            'rsvp_status'      => 'yes',
            'guests_confirmed' => 1,
        ]);

        $response->assertStatus(404);
    }
}
