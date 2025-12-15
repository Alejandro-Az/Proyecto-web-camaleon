<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventGift;
use App\Models\EventGiftClaim;
use App\Models\Guest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GiftClaimTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function invitado_puede_apartar_un_regalo_disponible()
    {
        $event = Event::factory()->create([
            'slug'    => 'evento-regalo-disponible',
            'status'  => Event::STATUS_ACTIVE,
            'modules' => [
                'gifts' => true,
            ],
            'settings' => [
                'gifts_require_invitation_code'      => true,
                'gifts_max_units_per_guest_per_gift' => 1,
            ],
        ]);

        $guest = Guest::factory()->create([
            'event_id'        => $event->id,
            'invitation_code' => 'GIFTCODE1',
        ]);

        $gift = EventGift::create([
            'event_id'          => $event->id,
            'name'              => 'Juego de vasos',
            'quantity'          => 2,
            'quantity_reserved' => 0,
            'status'            => EventGift::STATUS_PENDING,
            'display_order'     => 1,
        ]);

        $response = $this->postJson(
            route('events.gifts.reserve', ['slug' => $event->slug, 'gift' => $gift->id]),
            [
                'invitation_code' => $guest->invitation_code,
            ]
        );

        $response
            ->assertStatus(200)
            ->assertJsonFragment([
                'gift_id'           => $gift->id,
                'status'            => EventGift::STATUS_RESERVED,
                'quantity'          => 2,
                'quantity_reserved' => 1,
            ]);

        $this->assertDatabaseHas('event_gift_claims', [
            'event_id' => $event->id,
            'gift_id'  => $gift->id,
            'guest_id' => $guest->id,
            'status'   => EventGiftClaim::STATUS_RESERVED,
        ]);
    }

    /** @test */
    public function no_permite_apartar_si_ya_no_hay_unidades_disponibles()
    {
        $event = Event::factory()->create([
            'slug'    => 'evento-sin-unidades',
            'status'  => Event::STATUS_ACTIVE,
            'modules' => [
                'gifts' => true,
            ],
            'settings' => [
                'gifts_require_invitation_code'      => true,
                'gifts_max_units_per_guest_per_gift' => 1,
            ],
        ]);

        $guest = Guest::factory()->create([
            'event_id'        => $event->id,
            'invitation_code' => 'SINUNIDADES',
        ]);

        $gift = EventGift::create([
            'event_id'          => $event->id,
            'name'              => 'Tostador agotado',
            'quantity'          => 1,
            'quantity_reserved' => 1,
            'status'            => EventGift::STATUS_RESERVED,
            'display_order'     => 1,
        ]);

        $response = $this->postJson(
            route('events.gifts.reserve', ['slug' => $event->slug, 'gift' => $gift->id]),
            [
                'invitation_code' => $guest->invitation_code,
            ]
        );

        $response
            ->assertStatus(422)
            ->assertJsonFragment([
                'message' => 'Este regalo ya no tiene unidades disponibles.',
            ]);
    }

    /** @test */
    public function no_permite_apartar_si_el_regalo_esta_marcado_como_purchased()
    {
        $event = Event::factory()->create([
            'slug'    => 'evento-regalo-comprado',
            'status'  => Event::STATUS_ACTIVE,
            'modules' => [
                'gifts' => true,
            ],
            'settings' => [
                'gifts_require_invitation_code'      => true,
                'gifts_max_units_per_guest_per_gift' => 1,
            ],
        ]);

        $guest = Guest::factory()->create([
            'event_id'        => $event->id,
            'invitation_code' => 'COMPRADO1',
        ]);

        $gift = EventGift::create([
            'event_id'          => $event->id,
            'name'              => 'Regalo ya comprado',
            'quantity'          => 1,
            'quantity_reserved' => 1,
            'status'            => EventGift::STATUS_PURCHASED,
            'display_order'     => 1,
        ]);

        $response = $this->postJson(
            route('events.gifts.reserve', ['slug' => $event->slug, 'gift' => $gift->id]),
            [
                'invitation_code' => $guest->invitation_code,
            ]
        );

        $response
            ->assertStatus(422)
            ->assertJsonFragment([
                'message' => 'Este regalo ya fue confirmado como comprado.',
            ]);
    }

    /** @test */
    public function requiere_codigo_de_invitacion_valido_si_la_configuracion_lo_exige()
    {
        $event = Event::factory()->create([
            'slug'    => 'evento-codigo-obligatorio',
            'status'  => Event::STATUS_ACTIVE,
            'modules' => [
                'gifts' => true,
            ],
            'settings' => [
                'gifts_require_invitation_code'      => true,
                'gifts_max_units_per_guest_per_gift' => 1,
            ],
        ]);

        $gift = EventGift::create([
            'event_id'          => $event->id,
            'name'              => 'Regalo X',
            'quantity'          => 1,
            'quantity_reserved' => 0,
            'status'            => EventGift::STATUS_PENDING,
            'display_order'     => 1,
        ]);

        $response = $this->postJson(
            route('events.gifts.reserve', ['slug' => $event->slug, 'gift' => $gift->id]),
            [
                'invitation_code' => 'CODIGO-INVALIDO',
            ]
        );

        $response
            ->assertStatus(422)
            ->assertJsonFragment([
                'message' => 'No pudimos identificar su invitación. Use el enlace personal que recibió.',
            ]);

        $this->assertDatabaseCount('event_gift_claims', 0);
    }

    /** @test */
    public function no_permite_doble_reserva_del_mismo_regalo_por_el_mismo_invitado_cuando_max_es_1()
    {
        $event = Event::factory()->create([
            'slug'    => 'evento-reserva-doble',
            'status'  => Event::STATUS_ACTIVE,
            'modules' => [
                'gifts' => true,
            ],
            'settings' => [
                'gifts_require_invitation_code'      => true,
                'gifts_max_units_per_guest_per_gift' => 1,
            ],
        ]);

        $guest = Guest::factory()->create([
            'event_id'        => $event->id,
            'invitation_code' => 'DOBLE1',
        ]);

        $gift = EventGift::create([
            'event_id'          => $event->id,
            'name'              => 'Regalo reservado',
            'quantity'          => 2,
            'quantity_reserved' => 0,
            'status'            => EventGift::STATUS_PENDING,
            'display_order'     => 1,
        ]);

        // Primera reserva (ok)
        $this->postJson(
            route('events.gifts.reserve', ['slug' => $event->slug, 'gift' => $gift->id]),
            [
                'invitation_code' => $guest->invitation_code,
            ]
        )->assertStatus(200);

        // Segunda reserva (debe fallar por límite)
        $response = $this->postJson(
            route('events.gifts.reserve', ['slug' => $event->slug, 'gift' => $gift->id]),
            [
                'invitation_code' => $guest->invitation_code,
            ]
        );

        $response
            ->assertStatus(422)
            ->assertJsonFragment([
                'message' => 'Ya te comprometiste con este regalo.',
            ]);
    }

    /** @test */
    public function no_permite_reservar_regalos_de_otro_evento()
    {
        $event1 = Event::factory()->create([
            'slug'    => 'evento-uno',
            'status'  => Event::STATUS_ACTIVE,
            'modules' => [
                'gifts' => true,
            ],
        ]);

        $event2 = Event::factory()->create([
            'slug'    => 'evento-dos',
            'status'  => Event::STATUS_ACTIVE,
            'modules' => [
                'gifts' => true,
            ],
        ]);

        $guest = Guest::factory()->create([
            'event_id'        => $event1->id,
            'invitation_code' => 'EVENTO1',
        ]);

        $giftEvent2 = EventGift::create([
            'event_id'          => $event2->id,
            'name'              => 'Regalo de evento 2',
            'quantity'          => 1,
            'quantity_reserved' => 0,
            'status'            => EventGift::STATUS_PENDING,
            'display_order'     => 1,
        ]);

        // Intentamos reservar usando slug del evento1 con gift de evento2
        $response = $this->postJson(
            route('events.gifts.reserve', ['slug' => $event1->slug, 'gift' => $giftEvent2->id]),
            [
                'invitation_code' => $guest->invitation_code,
            ]
        );

        $response->assertStatus(404);
    }

    /** @test */
    public function no_permite_reservar_si_el_modulo_gifts_esta_desactivado()
    {
        $event = Event::factory()->create([
            'slug'    => 'evento-gifts-off',
            'status'  => Event::STATUS_ACTIVE,
            'modules' => [
                'gifts' => false,
            ],
        ]);

        $guest = Guest::factory()->create([
            'event_id'        => $event->id,
            'invitation_code' => 'OFF1',
        ]);

        $gift = EventGift::create([
            'event_id'          => $event->id,
            'name'              => 'Regalo no disponible',
            'quantity'          => 1,
            'quantity_reserved' => 0,
            'status'            => EventGift::STATUS_PENDING,
            'display_order'     => 1,
        ]);

        $response = $this->postJson(
            route('events.gifts.reserve', ['slug' => $event->slug, 'gift' => $gift->id]),
            [
                'invitation_code' => $guest->invitation_code,
            ]
        );

        $response->assertStatus(404);
    }
}
