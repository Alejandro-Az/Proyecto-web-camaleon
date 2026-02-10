<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientPanelEventShowTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function cliente_puede_ver_su_evento(): void
    {
        $clientA = User::factory()->create(['role' => 'client']);
        $eventA  = Event::factory()->create([
            'owner_user_id' => $clientA->id,
            'name' => 'Evento A',
        ]);

        $this->actingAs($clientA, 'client')
            ->get('/panel/eventos/' . $eventA->id)
            ->assertStatus(200)
            ->assertSee('Evento A')
            ->assertSee('Volver a mis eventos');
    }

    /** @test */
    public function cliente_no_puede_ver_evento_ajeno_y_recibe_404(): void
    {
        $clientA = User::factory()->create(['role' => 'client']);
        $clientB = User::factory()->create(['role' => 'client']);

        $eventB = Event::factory()->create([
            'owner_user_id' => $clientB->id,
            'name' => 'Evento B',
        ]);

        $this->actingAs($clientA, 'client')
            ->get('/panel/eventos/' . $eventB->id)
            ->assertStatus(404);
    }
}
