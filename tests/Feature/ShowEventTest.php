<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventLocation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowEventTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function slug_inexistente_devuelve_404(): void
    {
        $this->get('/eventos/slug-que-no-existe')
            ->assertStatus(404);
    }

    /** @test */
    public function evento_inactivo_no_se_muestra_publicamente(): void
    {
        // status != active → publicVisible scope lo excluye
        $event = Event::factory()->create([
            'slug'   => 'evento-inactivo',
            'status' => 'draft',
        ]);

        $this->get("/eventos/{$event->slug}")
            ->assertStatus(404);
    }

    /** @test */
    public function puede_mostrar_un_evento_activo_por_slug()
    {
        // Arrange
        $event = Event::factory()->create([
            'type'   => 'wedding',
            'name'   => 'Boda Test',
            'slug'   => 'boda-test',
            'status' => Event::STATUS_ACTIVE,
        ]);

        EventLocation::factory()->create([
            'event_id' => $event->id,
            'type'     => 'ceremony',
            'name'     => 'Iglesia Test',
        ]);

        // Act
        $response = $this->get('/eventos/' . $event->slug);

        // Assert
        $response->assertStatus(200);
        $response->assertSee('Boda Test');
        $response->assertSee('Iglesia Test');
    }
}
