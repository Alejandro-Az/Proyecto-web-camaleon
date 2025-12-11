<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventPhoto;
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

        $event = Event::factory()->create([
            'type'   => 'wedding',
            'name'   => 'Evento con Galería',
            'slug'   => 'evento-con-galeria',
            'status' => Event::STATUS_ACTIVE,
        ]);

        $file = UploadedFile::fake()->image('foto-demo.jpg', 800, 600);

        $response = $this->postJson(
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
        ]);

        $photo = EventPhoto::first();
        $this->assertNotNull($photo);

        Storage::disk('public')->assertExists($photo->file_path);
    }
}
