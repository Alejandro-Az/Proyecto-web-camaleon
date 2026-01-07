<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientPanelAuthTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function invitado_es_redirigido_a_login_del_panel(): void
    {
        $this->get('/panel/eventos')
            ->assertStatus(302)
            ->assertRedirect('/panel/login');
    }

    /** @test */
    public function usuario_admin_no_puede_entrar_al_panel_cliente(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin, 'client')
            ->get('/panel/eventos')
            ->assertStatus(403);
    }
}
