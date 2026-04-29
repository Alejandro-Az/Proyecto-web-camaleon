<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JwtAuthApiTest extends TestCase
{
    use RefreshDatabase;

    // ─── login ────────────────────────────────────────────────────────────────

    /** @test */
    public function login_con_credenciales_validas_retorna_token(): void
    {
        $admin = User::factory()->admin()->create(['password' => 'password']);

        $this->postJson('/api/auth/login', [
            'email'    => $admin->email,
            'password' => 'password',
        ])
            ->assertStatus(200)
            ->assertJsonStructure(['access_token', 'token_type', 'expires_in'])
            ->assertJsonFragment(['token_type' => 'bearer']);
    }

    /** @test */
    public function login_con_credenciales_invalidas_retorna_401(): void
    {
        User::factory()->admin()->create(['email' => 'admin@test.com', 'password' => 'secret']);

        $this->postJson('/api/auth/login', [
            'email'    => 'admin@test.com',
            'password' => 'wrong-password',
        ])->assertStatus(401);
    }

    /** @test */
    public function login_sin_campos_requeridos_retorna_422(): void
    {
        $this->postJson('/api/auth/login', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    // ─── me ───────────────────────────────────────────────────────────────────

    /** @test */
    public function me_con_token_valido_retorna_datos_del_usuario(): void
    {
        $admin = User::factory()->admin()->create();
        $token = auth('api')->login($admin);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/auth/me')
            ->assertStatus(200)
            ->assertJsonFragment([
                'id'    => $admin->id,
                'email' => $admin->email,
                'role'  => 'admin',
            ]);
    }

    /** @test */
    public function me_sin_token_retorna_401(): void
    {
        $this->getJson('/api/auth/me')
            ->assertStatus(401);
    }

    // ─── logout ───────────────────────────────────────────────────────────────

    /** @test */
    public function logout_con_token_valido_retorna_ok(): void
    {
        $admin = User::factory()->admin()->create();
        $token = auth('api')->login($admin);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/auth/logout')
            ->assertStatus(200)
            ->assertJsonFragment(['message' => 'Logout OK.']);
    }

    /** @test */
    public function logout_sin_token_retorna_401(): void
    {
        $this->postJson('/api/auth/logout')
            ->assertStatus(401);
    }

    // ─── refresh ──────────────────────────────────────────────────────────────

    /** @test */
    public function refresh_con_token_valido_retorna_nuevo_token(): void
    {
        $admin = User::factory()->admin()->create();
        $token = auth('api')->login($admin);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/auth/refresh')
            ->assertStatus(200)
            ->assertJsonStructure(['access_token', 'token_type', 'expires_in']);

        // El nuevo token debe ser distinto al original
        $this->assertNotEquals($token, $response->json('access_token'));
    }

    /** @test */
    public function refresh_sin_token_retorna_401(): void
    {
        $this->postJson('/api/auth/refresh')
            ->assertStatus(401);
    }
}
