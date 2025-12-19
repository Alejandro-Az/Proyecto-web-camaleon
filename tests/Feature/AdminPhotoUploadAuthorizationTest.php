<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminPhotoUploadAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function sin_token_regresa_401(): void
    {
        $event = Event::factory()->create();

        // postJson asegura Accept: application/json (evita redirects)
        $this->postJson(route('admin.events.photos.store', ['event' => $event->id]), [])
            ->assertStatus(401);
    }

    /** @test */
    public function con_token_client_regresa_403(): void
    {
        $event = Event::factory()->create();

        $client = User::factory()->client()->create([
            'password' => 'password',
        ]);

        $token = auth('api')->login($client);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson(route('admin.events.photos.store', ['event' => $event->id]), [])
            ->assertStatus(403);
    }

    /** @test */
    public function con_token_admin_permite_201(): void
    {
        Storage::fake('public');

        $event = Event::factory()->create();

        $admin = User::factory()->admin()->create([
            'password' => 'password',
        ]);

        $token = auth('api')->login($admin);

        $file = UploadedFile::fake()->image('foto.jpg', 1200, 800);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->withHeader('Accept', 'application/json')
            ->post(route('admin.events.photos.store', ['event' => $event->id]), [
                'photo' => $file,
                'type'  => 'gallery',
            ])
            ->assertStatus(201);
    }
}
