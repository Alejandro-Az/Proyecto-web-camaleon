<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventPhoto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadEventPhotoTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function puede_subir_una_foto_a_la_galeria_del_evento()
    {
        Storage::fake('public');

        $admin = User::factory()->admin()->create([
            'password' => 'password',
        ]);

        $token = auth('api')->login($admin);

        $event = Event::factory()->create([
            'type'   => 'wedding',
            'name'   => 'Evento con Galería',
            'slug'   => 'evento-con-galeria',
            'status' => Event::STATUS_ACTIVE,
        ]);

        $file = UploadedFile::fake()->image('foto-demo.jpg', 800, 600);

        $response = $this
            ->withHeader('Authorization', "Bearer {$token}")
            ->withHeader('Accept', 'application/json')
            ->post(
                route('admin.events.photos.store', ['event' => $event->id]),
                [
                    'photo'   => $file,
                    'type'    => 'gallery',
                    'caption' => 'Foto subida desde test',
                ]
            );

        $response
            ->assertStatus(201)
            ->assertJsonFragment([
                'event_id' => $event->id,
                'type'     => 'gallery',
                'caption'  => 'Foto subida desde test',
            ]);

        $this->assertDatabaseHas('event_photos', [
            'event_id' => $event->id,
            'type'     => 'gallery',
            'caption'  => 'Foto subida desde test',
            'status'   => EventPhoto::STATUS_APPROVED,
        ]);

        $photo = EventPhoto::query()->where('event_id', $event->id)->first();
        $this->assertNotNull($photo);

        Storage::disk('public')->assertExists($photo->file_path);
    }
}
