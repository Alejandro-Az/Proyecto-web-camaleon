<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventGift;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowEventGiftsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function muestra_los_regalos_cuando_el_modulo_esta_activo()
    {
        $event = Event::factory()->create([
            'slug'    => 'evento-con-regalos',
            'status'  => Event::STATUS_ACTIVE,
            'modules' => [
                'gifts' => true,
            ],
        ]);

        EventGift::create([
            'event_id'          => $event->id,
            'name'              => 'Tostador',
            'quantity'          => 1,
            'quantity_reserved' => 0,
            'status'            => EventGift::STATUS_PENDING,
            'display_order'     => 1,
        ]);

        $response = $this->get('/eventos/' . $event->slug);

        $response->assertStatus(200);
        $response->assertSee('Mesa de regalos');
        $response->assertSee('Tostador');
    }

    /** @test */
    public function no_muestra_regalos_si_el_modulo_esta_desactivado()
    {
        $event = Event::factory()->create([
            'slug'    => 'evento-sin-regalos',
            'status'  => Event::STATUS_ACTIVE,
            'modules' => [
                'gifts' => false,
            ],
        ]);

        EventGift::create([
            'event_id'          => $event->id,
            'name'              => 'Tostador oculto',
            'quantity'          => 1,
            'quantity_reserved' => 0,
            'status'            => EventGift::STATUS_PENDING,
            'display_order'     => 1,
        ]);

        $response = $this->get('/eventos/' . $event->slug);

        $response->assertStatus(200);
        $response->assertDontSee('Mesa de regalos');
        $response->assertDontSee('Tostador oculto');
    }

    /** @test */
    public function oculta_los_regalos_purchased_si_la_configuracion_lo_indica()
    {
        $event = Event::factory()->create([
            'slug'    => 'evento-oculta-regalos-comprados',
            'status'  => Event::STATUS_ACTIVE,
            'modules' => [
                'gifts' => true,
            ],
            'settings' => [
                'gifts_hide_purchased_from_public' => true,
            ],
        ]);

        EventGift::create([
            'event_id'          => $event->id,
            'name'              => 'Regalo comprado',
            'quantity'          => 1,
            'quantity_reserved' => 1,
            'status'            => EventGift::STATUS_PURCHASED,
            'display_order'     => 1,
        ]);

        $response = $this->get('/eventos/' . $event->slug);

        $response->assertStatus(200);
        $response->assertSee('Mesa de regalos');
        $response->assertDontSee('Regalo comprado');
    }
}
