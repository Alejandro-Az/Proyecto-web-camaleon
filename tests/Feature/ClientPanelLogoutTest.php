<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientPanelLogoutTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function cliente_puede_cerrar_sesion_y_es_redirigido_al_login(): void
    {
        $client = User::factory()->create(['role' => 'client']);

        $this->actingAs($client, 'client')
            ->post(route('client.logout'))
            ->assertRedirect('/panel/login');
    }

    /** @test */
    public function tras_logout_la_sesion_queda_invalidada(): void
    {
        $client = User::factory()->create(['role' => 'client']);

        // Hacemos logout
        $this->actingAs($client, 'client')
            ->post(route('client.logout'));

        // El guard client ya no tiene usuario autenticado
        $this->assertGuest('client');
    }

    /** @test */
    public function tras_logout_no_se_puede_acceder_al_panel(): void
    {
        $client = User::factory()->create(['role' => 'client']);

        $this->actingAs($client, 'client')
            ->post(route('client.logout'));

        // Un request posterior sin sesión debe redirigir a login
        $this->get(route('client.events.index'))
            ->assertRedirect(route('client.login'));
    }
}
