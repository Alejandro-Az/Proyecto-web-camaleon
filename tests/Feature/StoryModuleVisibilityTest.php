<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventStory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class StoryModuleVisibilityTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function muestra_historia_cuando_modulo_activo_y_hay_data(): void
    {
        $event = Event::create([
            'type'       => 'wedding',
            'name'       => 'Evento Story ON',
            'slug'       => 'evento-story-on',
            'status'     => Event::STATUS_ACTIVE,
            'event_date' => Carbon::now()->addDays(10),
            'start_time' => '18:00:00',
            'end_time'   => '20:00:00',
            'theme_key'       => 'romantic',
            'primary_color'   => '#f472b6',
            'secondary_color' => '#0f172a',
            'accent_color'    => '#f9a8d4',
            'font_family'     => 'Playfair Display',
            'modules'    => Event::normalizeModulesForStorage(['story' => true]),
            'settings'   => [],
            'owner_name'  => 'Demo',
            'owner_email' => 'demo@example.com',
            'auto_cleanup_after_days' => 30,
        ]);

        EventStory::create([
            'event_id'      => $event->id,
            'title'         => 'Nuestra historia',
            'subtitle'      => 'Cómo empezó todo',
            'body'          => 'Texto demo',
            'example_photo_id' => null,
            'display_order' => 1,
            'is_enabled'    => true,
        ]);

        $response = $this->get('/eventos/' . $event->slug);

        $response->assertStatus(200);
        $response->assertSee('Historia');
        $response->assertSee('Nuestra historia');
    }

    /** @test */
    public function no_muestra_historia_si_el_modulo_esta_apagado_aunque_haya_data(): void
    {
        $event = Event::create([
            'type'       => 'wedding',
            'name'       => 'Evento Story OFF',
            'slug'       => 'evento-story-off',
            'status'     => Event::STATUS_ACTIVE,
            'event_date' => Carbon::now()->addDays(10),
            'start_time' => '18:00:00',
            'end_time'   => '20:00:00',
            'theme_key'       => 'romantic',
            'primary_color'   => '#f472b6',
            'secondary_color' => '#0f172a',
            'accent_color'    => '#f9a8d4',
            'font_family'     => 'Playfair Display',
            'modules'    => Event::normalizeModulesForStorage(['story' => false]),
            'settings'   => [],
            'owner_name'  => 'Demo',
            'owner_email' => 'demo@example.com',
            'auto_cleanup_after_days' => 30,
        ]);

        EventStory::create([
            'event_id'      => $event->id,
            'title'         => 'No debo salir',
            'subtitle'      => null,
            'body'          => 'Texto demo',
            'example_photo_id' => null,
            'display_order' => 1,
            'is_enabled'    => true,
        ]);

        $response = $this->get('/eventos/' . $event->slug);

        $response->assertStatus(200);
        $response->assertDontSee('Historia');
        $response->assertDontSee('No debo salir');
    }
}
