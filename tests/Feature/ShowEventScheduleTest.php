<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowEventScheduleTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function muestra_el_itinerario_cuando_el_modulo_esta_activo()
    {
        $event = Event::factory()->create([
            'type'   => 'wedding',
            'name'   => 'Evento con Itinerario',
            'slug'   => 'evento-con-itinerario',
            'status' => Event::STATUS_ACTIVE,
            'modules' => [
                'schedule' => true,
            ],
        ]);

        EventSchedule::create([
            'event_id'       => $event->id,
            'title'          => 'Ceremonia religiosa',
            'description'    => 'Descripción demo 1',
            'starts_at'      => now()->setTime(18, 0),
            'ends_at'        => now()->setTime(19, 0),
            'location_label' => 'Iglesia Demo',
            'location_type'  => 'ceremony',
            'display_order'  => 1,
        ]);

        EventSchedule::create([
            'event_id'       => $event->id,
            'title'          => 'Banquete',
            'description'    => 'Descripción demo 2',
            'starts_at'      => now()->setTime(20, 0),
            'ends_at'        => now()->setTime(21, 30),
            'location_label' => 'Salón Demo',
            'location_type'  => 'reception',
            'display_order'  => 2,
        ]);

        $response = $this->get('/eventos/' . $event->slug);

        $response->assertStatus(200);
        $response->assertSee('Itinerario del evento');
        $response->assertSee('Ceremonia religiosa');
        $response->assertSee('Banquete');

        $response->assertSeeInOrder([
            'Ceremonia religiosa',
            'Banquete',
        ]);
    }

    /** @test */
    public function no_muestra_el_itinerario_si_el_modulo_esta_desactivado()
    {
        $event = Event::factory()->create([
            'type'   => 'wedding',
            'name'   => 'Evento sin Itinerario',
            'slug'   => 'evento-sin-itinerario',
            'status' => Event::STATUS_ACTIVE,
            'modules' => [
                'schedule' => false,
            ],
        ]);

        EventSchedule::create([
            'event_id'       => $event->id,
            'title'          => 'Ceremonia religiosa',
            'description'    => 'Descripción demo',
            'starts_at'      => now()->setTime(18, 0),
            'ends_at'        => now()->setTime(19, 0),
            'location_label' => 'Iglesia Demo',
            'location_type'  => 'ceremony',
            'display_order'  => 1,
        ]);

        $response = $this->get('/eventos/' . $event->slug);

        $response->assertStatus(200);
        $response->assertDontSee('Itinerario del evento');
        $response->assertDontSee('Ceremonia religiosa');
    }
}
