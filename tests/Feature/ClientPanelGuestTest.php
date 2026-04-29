<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Guest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientPanelGuestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function cliente_puede_ver_lista_de_invitados(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $event = Event::factory()->create(['owner_user_id' => $client->id]);
        
        Guest::factory()->count(3)->create(['event_id' => $event->id]);

        $this->actingAs($client, 'client')
            ->get(route('client.events.guests.index', $event->id))
            ->assertStatus(200)
            ->assertViewHas('guests');
    }

    /** @test */
    public function cliente_no_puede_ver_invitados_ajenos(): void
    {
        $clientA = User::factory()->create(['role' => 'client']);
        $clientB = User::factory()->create(['role' => 'client']);
        $eventB = Event::factory()->create(['owner_user_id' => $clientB->id]);

        $this->actingAs($clientA, 'client')
            ->get(route('client.events.guests.index', $eventB->id))
            ->assertStatus(403);
    }

    /** @test */
    public function cliente_puede_crear_invitado(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $event = Event::factory()->create(['owner_user_id' => $client->id]);

        $data = [
            'name' => 'Tía Paquita',
            'invited_seats' => 2,
            'phone' => '555-1234',
        ];

        $this->actingAs($client, 'client')
            ->post(route('client.events.guests.store', $event->id), $data)
            ->assertStatus(302); // Redirect back or to index

        $this->assertDatabaseHas('guests', [
            'event_id' => $event->id,
            'name' => 'Tía Paquita',
            'invited_seats' => 2,
        ]);
        
        // Assert code was generated
        $guest = Guest::where('name', 'Tía Paquita')->first();
        $this->assertNotNull($guest->invitation_code);
    }

    /** @test */
    public function cliente_puede_editar_invitado_y_rsvp(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $event = Event::factory()->create(['owner_user_id' => $client->id]);
        $guest = Guest::factory()->create([
            'event_id' => $event->id,
            'invited_seats' => 2,
            'guests_confirmed' => 0,
            'rsvp_status' => 'pending'
        ]);

        $data = [
            'name' => 'Tía Paquita Editada',
            'invited_seats' => 3,
            'invitation_code' => $guest->invitation_code,
            'email' => 'paquita@test.com',
            'rsvp_status' => 'yes',
            'guests_confirmed' => 2,
            'show_in_public_list' => true
        ];

        $this->actingAs($client, 'client')
            ->put(route('client.guests.update', $guest->id), $data)
            ->assertStatus(302);

        $this->assertDatabaseHas('guests', [
            'id' => $guest->id,
            'name' => 'Tía Paquita Editada',
            'invited_seats' => 3, // Changed from 2
            'guests_confirmed' => 2,
            'rsvp_status' => 'yes',
            'show_in_public_list' => 1,
        ]);
    }

    /** @test */
    public function cliente_puede_eliminar_invitado(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $event = Event::factory()->create(['owner_user_id' => $client->id]);
        $guest = Guest::factory()->create(['event_id' => $event->id]);

        $this->actingAs($client, 'client')
            ->delete(route('client.guests.destroy', $guest->id))
            ->assertStatus(302);

        $this->assertSoftDeleted('guests', ['id' => $guest->id]);
    }

    /** @test */
    public function no_permite_confirmar_mas_invitados_de_los_asignados(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $event = Event::factory()->create(['owner_user_id' => $client->id]);
        $guest = Guest::factory()->create([
            'event_id' => $event->id,
            'invited_seats' => 2
        ]);

        $this->actingAs($client, 'client')
            ->put(route('client.guests.update', $guest->id), [
                'name' => $guest->name,
                'invited_seats' => 2,
                'invitation_code' => $guest->invitation_code,
                'rsvp_status' => 'yes',
                'guests_confirmed' => 5 // Error: > 2
            ])
            ->assertSessionHasErrors('guests_confirmed');
    }
}
