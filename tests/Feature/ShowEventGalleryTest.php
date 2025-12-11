<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventPhoto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowEventGalleryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function muestra_la_galeria_cuando_hay_fotos_y_modulo_activo()
    {
        $event = Event::factory()->create([
            'type'    => 'wedding',
            'name'    => 'Boda con Galería',
            'slug'    => 'boda-con-galeria',
            'status'  => Event::STATUS_ACTIVE,
            'modules' => [
                'gallery' => true,
            ],
        ]);

        EventPhoto::create([
            'event_id'      => $event->id,
            'type'          => EventPhoto::TYPE_GALLERY,
            'file_path'     => 'events/'.$event->id.'/photos/originals/foto1.jpg',
            'thumbnail_path'=> 'events/'.$event->id.'/photos/thumbnails/foto1_thumb.jpg',
            'caption'       => 'Foto de prueba 1',
            'status'        => EventPhoto::STATUS_APPROVED,
            'display_order' => 1,
        ]);

        $response = $this->get('/eventos/'.$event->slug);

        $response->assertStatus(200);
        $response->assertSee('Galería de fotos');
        $response->assertSee('Foto de prueba 1');
    }

    /** @test */
    public function no_muestra_la_galeria_si_el_modulo_esta_desactivado()
    {
        $event = Event::factory()->create([
            'type'    => 'wedding',
            'name'    => 'Boda sin Galería',
            'slug'    => 'boda-sin-galeria',
            'status'  => Event::STATUS_ACTIVE,
            'modules' => [
                'gallery' => false,
            ],
        ]);

        EventPhoto::create([
            'event_id'      => $event->id,
            'type'          => EventPhoto::TYPE_GALLERY,
            'file_path'     => 'events/'.$event->id.'/photos/originals/foto1.jpg',
            'thumbnail_path'=> null,
            'caption'       => 'Foto que no debería verse',
            'status'        => EventPhoto::STATUS_APPROVED,
            'display_order' => 1,
        ]);

        $response = $this->get('/eventos/'.$event->slug);

        $response->assertStatus(200);
        $response->assertDontSee('Galería de fotos');
        $response->assertDontSee('Foto que no debería verse');
    }

    /** @test */
    public function no_muestra_fotos_no_aprobadas_en_la_galeria()
    {
        $event = Event::factory()->create([
            'type'    => 'wedding',
            'name'    => 'Boda Galería Estados',
            'slug'    => 'boda-galeria-estados',
            'status'  => Event::STATUS_ACTIVE,
            'modules' => [
                'gallery' => true,
            ],
        ]);

        // Foto aprobada
        EventPhoto::create([
            'event_id'      => $event->id,
            'type'          => EventPhoto::TYPE_GALLERY,
            'file_path'     => 'events/'.$event->id.'/photos/originals/aprobada.jpg',
            'caption'       => 'Foto aprobada',
            'status'        => EventPhoto::STATUS_APPROVED,
            'display_order' => 1,
        ]);

        // Foto pendiente que NO debe mostrarse
        EventPhoto::create([
            'event_id'      => $event->id,
            'type'          => EventPhoto::TYPE_GALLERY,
            'file_path'     => 'events/'.$event->id.'/photos/originals/pendiente.jpg',
            'caption'       => 'Foto pendiente',
            'status'        => EventPhoto::STATUS_PENDING,
            'display_order' => 2,
        ]);

        $response = $this->get('/eventos/'.$event->slug);

        $response->assertStatus(200);
        $response->assertSee('Foto aprobada');
        $response->assertDontSee('Foto pendiente');
    }
}
