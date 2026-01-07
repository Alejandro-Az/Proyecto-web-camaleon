<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientPanelEventsIndexTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function lista_solo_eventos_del_cliente_logueado()
    {
        $this->withoutExceptionHandling();

        $clientA = User::factory()->create(['role' => 'client']);
        $clientB = User::factory()->create(['role' => 'client']);

        Event::factory()->create(['owner_user_id' => $clientA->id, 'name' => 'Evento A']);
        Event::factory()->create(['owner_user_id' => $clientB->id, 'name' => 'Evento B']);

        $this->actingAs($clientA, 'client')
            ->get('/panel/eventos')
            ->assertStatus(200)
            ->assertSee('Evento A')
            ->assertDontSee('Evento B');
    }

}
