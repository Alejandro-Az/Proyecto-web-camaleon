<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ClientPanelAdminCannotLoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function un_admin_no_puede_iniciar_sesion_en_el_panel_cliente()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@camaleon.test',
            'password' => Hash::make('password'),
        ]);

        $response = $this->from('/panel/login')->post('/panel/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/panel/login');
        $response->assertSessionHasErrors('email');

        // Debe seguir como invitado en guard client
        $this->assertGuest('client');
    }
}
