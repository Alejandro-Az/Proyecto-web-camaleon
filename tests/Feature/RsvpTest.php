<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Guest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RsvpTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function invitado_puede_confirmar_su_asistencia()
    {
        $event = Event::factory()->create([
            'type'   => 'wedding',
            'name'   => 'Boda Test RSVP',
            'slug'   => 'boda-test-rsvp',
            'status' => Event::STATUS_ACTIVE,
        ]);

        $guest = Guest::factory()->create([
            'event_id'        => $event->id,
            'name'            => 'Invitado Ejemplo',
            'invitation_code' => 'ABC123',
        ]);

        $response = $this->post('/eventos/' . $event->slug . '/rsvp', [
            'invitation_code'     => 'ABC123',
            'rsvp_status'         => 'yes',
            'guests_confirmed'    => 2,
            'rsvp_message'        => '¡Nos vemos ahí!',
            'show_in_public_list' => 1,
        ]);

        $response->assertRedirect('/eventos/' . $event->slug . '?i=ABC123');

        $this->assertDatabaseHas('guests', [
            'id'                 => $guest->id,
            'event_id'           => $event->id,
            'invitation_code'    => 'ABC123',
            'rsvp_status'        => 'yes',
            'guests_confirmed'   => 2,
            'show_in_public_list'=> 1,
        ]);
    }

    /** @test */
    public function invitado_que_no_asistira_tiene_cero_personas_confirmadas()
    {
        $event = Event::factory()->create([
            'type'   => 'wedding',
            'name'   => 'Boda Test NO RSVP',
            'slug'   => 'boda-test-no-rsvp',
            'status' => Event::STATUS_ACTIVE,
        ]);

        $guest = Guest::factory()->create([
            'event_id'        => $event->id,
            'name'            => 'Invitado No Asiste',
            'invitation_code' => 'NO123456',
        ]);

        // Intenta mandar 5 personas pero responde "no"
        $response = $this->post('/eventos/' . $event->slug . '/rsvp', [
            'invitation_code'     => 'NO123456',
            'rsvp_status'         => 'no',
            'guests_confirmed'    => 5,
            'rsvp_message'        => 'Lo siento, no podré ir.',
            'show_in_public_list' => 1,
        ]);

        $response->assertRedirect('/eventos/' . $event->slug . '?i=NO123456');

        $this->assertDatabaseHas('guests', [
            'id'               => $guest->id,
            'event_id'         => $event->id,
            'invitation_code'  => 'NO123456',
            'rsvp_status'      => 'no',
            'guests_confirmed' => 0, // forzado a 0
        ]);
    }

        /** @test */
    public function no_puede_confirmar_mas_personas_que_los_asientos_invitados()
    {
        $event = Event::factory()->create([
            'type'   => 'wedding',
            'name'   => 'Boda Test Límite Asientos',
            'slug'   => 'boda-test-limite-asientos',
            'status' => Event::STATUS_ACTIVE,
        ]);

        $guest = Guest::factory()->create([
            'event_id'        => $event->id,
            'name'            => 'Invitado Limitado',
            'invitation_code' => 'LIM12345',
            'invited_seats'   => 2,
        ]);

        $response = $this
            ->from('/eventos/' . $event->slug . '?i=LIM12345&edit=1')
            ->post('/eventos/' . $event->slug . '/rsvp', [
                'invitation_code'     => 'LIM12345',
                'rsvp_status'         => 'yes',
                'guests_confirmed'    => 5, // intenta pasarse
                'rsvp_message'        => 'Vamos toda la familia',
                'show_in_public_list' => 1,
            ]);

        // Debe regresar a la página de edición con errores en guests_confirmed
        $response->assertRedirect('/eventos/' . $event->slug . '?i=LIM12345&edit=1');
        $response->assertSessionHasErrors('guests_confirmed');

        // Y NO debe haber quedado registrado el 5 en BD
        $this->assertDatabaseMissing('guests', [
            'id'               => $guest->id,
            'guests_confirmed' => 5,
        ]);
    }

}
