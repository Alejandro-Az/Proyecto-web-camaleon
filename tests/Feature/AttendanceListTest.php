<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Guest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function muestra_solo_invitados_confirmados_y_publicos_en_la_lista()
    {
        $event = Event::factory()->create([
            'type'    => 'wedding',
            'name'    => 'Boda Lista Demo',
            'slug'    => 'boda-lista-demo',
            'status'  => Event::STATUS_ACTIVE,
            'modules' => [
                'rsvp'                   => true,
                'public_attendance_list' => true,
            ],
        ]);

        // Invitado que debe aparecer
        Guest::factory()->create([
            'event_id'            => $event->id,
            'name'                => 'Invitado Público',
            'invitation_code'     => 'PUB12345',
            'rsvp_status'         => Guest::RSVP_YES,
            'guests_confirmed'    => 2,
            'show_in_public_list' => true,
        ]);

        // Confirmado pero oculto
        Guest::factory()->create([
            'event_id'            => $event->id,
            'name'                => 'Invitado Oculto',
            'invitation_code'     => 'HID12345',
            'rsvp_status'         => Guest::RSVP_YES,
            'guests_confirmed'    => 3,
            'show_in_public_list' => false,
        ]);

        // Público pero con NO asistencia
        Guest::factory()->create([
            'event_id'            => $event->id,
            'name'                => 'Invitado No Asiste',
            'invitation_code'     => 'NO12345',
            'rsvp_status'         => Guest::RSVP_NO,
            'guests_confirmed'    => 0,
            'show_in_public_list' => true,
        ]);

        $response = $this->get('/eventos/' . $event->slug);

        $response->assertStatus(200);

        // Debe ver solo al invitado público confirmado
        $response->assertSee('Invitado Público');
        $response->assertDontSee('Invitado Oculto');
        $response->assertDontSee('Invitado No Asiste');
    }
}
