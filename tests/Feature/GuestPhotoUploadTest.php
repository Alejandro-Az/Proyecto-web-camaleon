<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventPhoto;
use App\Models\Guest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GuestPhotoUploadTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function invitado_puede_subir_una_foto()
    {
        Storage::fake('public');

        $event = Event::factory()->create([
            'slug'    => 'evento-fotos',
            'status'  => Event::STATUS_ACTIVE,
            'modules' => [
                'guest_photos_upload' => true,
            ],
            'settings' => [
                'guest_photos_max_per_guest' => 5,
                'guest_photos_auto_approve'  => true,
            ],
        ]);

        $guest = Guest::factory()->create([
            'event_id'        => $event->id,
            'invitation_code' => 'FOTO123',
        ]);

        $file = UploadedFile::fake()->image('foto.jpg', 800, 600);

        $response = $this->post(
            route('events.guest-photos.store', ['slug' => $event->slug]),
            [
                'invitation_code' => $guest->invitation_code,
                'photo'           => $file,
                'caption'         => 'Foto de prueba',
            ],
            ['Accept' => 'application/json']
        );

        $response->assertStatus(200)
            ->assertJsonFragment([
                'message'       => 'Foto subida correctamente.',
                'auto_approved' => true,
            ]);

        $this->assertDatabaseHas('event_photos', [
            'event_id' => $event->id,
            'guest_id' => $guest->id,
            'type'     => 'guest_upload',
        ]);

        $photo = EventPhoto::first();
        Storage::disk('public')->assertExists($photo->file_path);
    }

    /** @test */
    public function respeta_el_limite_de_fotos_por_invitado()
    {
        Storage::fake('public');

        $event = Event::factory()->create([
            'slug'    => 'evento-fotos-limite',
            'status'  => Event::STATUS_ACTIVE,
            'modules' => [
                'guest_photos_upload' => true,
            ],
            'settings' => [
                'guest_photos_max_per_guest' => 1,
                'guest_photos_auto_approve'  => true,
            ],
        ]);

        $guest = Guest::factory()->create([
            'event_id'        => $event->id,
            'invitation_code' => 'LIMIT1',
        ]);

        EventPhoto::create([
            'event_id'       => $event->id,
            'guest_id'       => $guest->id,
            'type'           => 'guest_upload',
            'file_path'      => 'events/'.$event->id.'/guest-photos/originals/existente.jpg',
            'thumbnail_path' => null,
            'caption'        => 'Foto existente',
            'status'         => EventPhoto::STATUS_APPROVED,
            'display_order'  => 1,
        ]);

        $file = UploadedFile::fake()->image('nueva.jpg', 800, 600);

        $response = $this->post(
            route('events.guest-photos.store', ['slug' => $event->slug]),
            [
                'invitation_code' => $guest->invitation_code,
                'photo'           => $file,
            ],
            ['Accept' => 'application/json']
        );

        $response->assertStatus(422)
            ->assertJsonFragment([
                'message' => 'Ya ha subido el número máximo de fotos permitido para este evento.',
            ]);

        $this->assertEquals(1, EventPhoto::where('event_id', $event->id)
            ->where('guest_id', $guest->id)
            ->where('type', 'guest_upload')
            ->count());
    }

    /** @test */
    public function requiere_un_codigo_de_invitacion_valido_para_subir()
    {
        Storage::fake('public');

        $event = Event::factory()->create([
            'slug'    => 'evento-fotos-invalido',
            'status'  => Event::STATUS_ACTIVE,
            'modules' => [
                'guest_photos_upload' => true,
            ],
        ]);

        $file = UploadedFile::fake()->image('foto.jpg', 800, 600);

        $response = $this->post(
            route('events.guest-photos.store', ['slug' => $event->slug]),
            [
                'invitation_code' => 'CODIGO-INVALIDO',
                'photo'           => $file,
            ],
            ['Accept' => 'application/json']
        );

        $response->assertStatus(422)
            ->assertJsonFragment([
                'message' => 'No pudimos identificar su invitación. Use el enlace personal que recibió.',
            ]);

        $this->assertDatabaseCount('event_photos', 0);
    }

    /** @test */
    public function no_permite_subir_fotos_si_el_modulo_esta_desactivado()
    {
        Storage::fake('public');

        $event = Event::factory()->create([
            'slug'    => 'evento-fotos-desactivado',
            'status'  => Event::STATUS_ACTIVE,
            'modules' => [
                'guest_photos_upload' => false,
            ],
        ]);

        $guest = Guest::factory()->create([
            'event_id'        => $event->id,
            'invitation_code' => 'DESAC1',
        ]);

        $file = UploadedFile::fake()->image('foto.jpg', 800, 600);

        $response = $this->post(
            route('events.guest-photos.store', ['slug' => $event->slug]),
            [
                'invitation_code' => $guest->invitation_code,
                'photo'           => $file,
            ],
            ['Accept' => 'application/json']
        );

        $response->assertStatus(404);
        $this->assertDatabaseCount('event_photos', 0);
    }

    /** @test */
    public function marca_la_foto_como_pending_si_no_hay_auto_approve()
    {
        Storage::fake('public');

        $event = Event::factory()->create([
            'slug'    => 'evento-fotos-pending',
            'status'  => Event::STATUS_ACTIVE,
            'modules' => [
                'guest_photos_upload' => true,
            ],
            'settings' => [
                'guest_photos_max_per_guest' => 5,
                'guest_photos_auto_approve'  => false,
            ],
        ]);

        $guest = Guest::factory()->create([
            'event_id'        => $event->id,
            'invitation_code' => 'PEND1',
        ]);

        $file = UploadedFile::fake()->image('foto.jpg', 800, 600);

        $response = $this->post(
            route('events.guest-photos.store', ['slug' => $event->slug]),
            [
                'invitation_code' => $guest->invitation_code,
                'photo'           => $file,
            ],
            ['Accept' => 'application/json']
        );

        $response->assertStatus(200)
            ->assertJsonFragment([
                'auto_approved' => false,
            ]);

        $this->assertDatabaseHas('event_photos', [
            'event_id' => $event->id,
            'guest_id' => $guest->id,
            'type'     => 'guest_upload',
            'status'   => EventPhoto::STATUS_PENDING,
        ]);
    }
}
