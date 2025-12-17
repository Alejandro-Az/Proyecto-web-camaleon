<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventPhoto;
use App\Models\EventDressCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DressCodeImageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function muestra_imagen_de_ejemplos_en_codigo_de_vestimenta_cuando_existe(): void
    {
        $event = Event::factory()->create();

        // Aseguramos que el módulo esté activo
        $modules = is_array($event->modules) ? $event->modules : [];
        $modules['dress_code'] = true;
        $event->update(['modules' => $modules]);

        $photo = EventPhoto::create([
            'event_id'      => $event->id,
            'guest_id'      => null,
            'type'          => EventPhoto::TYPE_DRESS_CODE,
            'file_path'     => "events/{$event->id}/photos/originals/dc-formal.jpg",
            'thumbnail_path'=> null,
            'caption'       => 'Ejemplo formal',
            'status'        => EventPhoto::STATUS_APPROVED,
            'display_order' => 1,
        ]);

        $dressCode = EventDressCode::create([
            'event_id'         => $event->id,
            'title'            => 'Formal',
            'description'      => 'Traje / vestido formal.',
            'examples'         => "Traje oscuro, vestido largo, zapatos formales.",
            'notes'            => "Evitar tenis y mezclilla.",
            'icon'             => 'tie',
            'example_photo_id' => $photo->id,
            'display_order'    => 1,
            'is_enabled'       => true,
        ]);

        $response = $this->get('/eventos/' . $event->slug);

        $response->assertStatus(200);
        $response->assertSee('Código de vestimenta');
        $response->assertSee('Imagen de ejemplos');
        $response->assertSee('dc-formal.jpg'); // viene dentro del src generado por Storage::url()
    }
}
