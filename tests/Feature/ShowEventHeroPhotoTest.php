<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventPhoto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowEventHeroPhotoTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function muestra_la_foto_de_portada_cuando_existe_hero()
    {
        $event = Event::factory()->create([
            'type'   => 'wedding',
            'name'   => 'Evento con Hero',
            'slug'   => 'evento-con-hero',
            'status' => Event::STATUS_ACTIVE,
        ]);

        $photo = EventPhoto::create([
            'event_id'      => $event->id,
            'guest_id'      => null,
            'type'          => EventPhoto::TYPE_HERO,
            'file_path'     => "events/{$event->id}/photos/originals/hero-1.jpg",
            'thumbnail_path'=> null,
            'caption'       => 'Foto de portada de prueba',
            'status'        => EventPhoto::STATUS_APPROVED,
            'display_order' => 1,
        ]);

        $response = $this->get('/eventos/' . $event->slug);

        $response->assertStatus(200);
        // Verificamos que la ruta del archivo aparezca en el HTML (src de la imagen)
        $response->assertSee('hero-1.jpg');
        $response->assertSee('Foto de portada de prueba');
    }

    /** @test */
    public function no_revienta_si_no_hay_foto_hero()
    {
        $event = Event::factory()->create([
            'type'   => 'wedding',
            'name'   => 'Evento sin Hero',
            'slug'   => 'evento-sin-hero',
            'status' => Event::STATUS_ACTIVE,
        ]);

        $response = $this->get('/eventos/' . $event->slug);

        $response->assertStatus(200);
        $response->assertSee('Evento sin Hero');
    }
}
