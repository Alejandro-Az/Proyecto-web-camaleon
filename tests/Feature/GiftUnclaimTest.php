<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventGift;
use App\Models\EventGiftClaim;
use App\Models\Guest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GiftUnclaimTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function invitado_puede_liberar_su_reserva_de_regalo_cuando_unclaim_esta_habilitado()
    {
        $event = Event::factory()->create([
            'slug'    => 'evento-unclaim-ok',
            'status'  => Event::STATUS_ACTIVE,
            'modules' => [
                'gifts' => true,
            ],
            'settings' => [
                'gifts_require_invitation_code'      => true,
                'gifts_allow_unclaim'                => true,
                'gifts_max_units_per_guest_per_gift' => 1,
            ],
        ]);

        $guest = Guest::factory()->create([
            'event_id'        => $event->id,
            'invitation_code' => 'UNCLAIM1',
        ]);

        $gift = EventGift::create([
            'event_id'          => $event->id,
            'name'              => 'Juego de vasos',
            'quantity'          => 2,
            'quantity_reserved' => 1,
            'status'            => EventGift::STATUS_RESERVED,
            'display_order'     => 1,
        ]);

        $claim = EventGiftClaim::create([
            'event_id' => $event->id,
            'gift_id'  => $gift->id,
            'guest_id' => $guest->id,
            'quantity' => 1,
            'status'   => EventGiftClaim::STATUS_RESERVED,
        ]);

        $response = $this->postJson(
            route('events.gifts.unreserve', ['slug' => $event->slug, 'gift' => $gift->id]),
            [
                'invitation_code' => $guest->invitation_code,
            ]
        );

        $response
            ->assertStatus(200)
            ->assertJsonFragment([
                'gift_id'           => $gift->id,
                'quantity_reserved' => 0,
            ]);

        $gift->refresh();
        $this->assertEquals(0, $gift->quantity_reserved);
        $this->assertEquals(EventGift::STATUS_PENDING, $gift->status);

        $this->assertDatabaseHas('event_gift_claims', [
            'id'     => $claim->id,
            'status' => EventGiftClaim::STATUS_CANCELLED,
        ]);
    }

    /** @test */
    public function no_permite_liberar_si_unclaim_esta_desactivado()
    {
        $event = Event::factory()->create([
            'slug'    => 'evento-unclaim-off',
            'status'  => Event::STATUS_ACTIVE,
            'modules' => [
                'gifts' => true,
            ],
            'settings' => [
                'gifts_require_invitation_code'      => true,
                'gifts_allow_unclaim'                => false,
                'gifts_max_units_per_guest_per_gift' => 1,
            ],
        ]);

        $guest = Guest::factory()->create([
            'event_id'        => $event->id,
            'invitation_code' => 'UNCLAIMOFF',
        ]);

        $gift = EventGift::create([
            'event_id'          => $event->id,
            'name'              => 'Regalo X',
            'quantity'          => 1,
            'quantity_reserved' => 1,
            'status'            => EventGift::STATUS_RESERVED,
            'display_order'     => 1,
        ]);

        EventGiftClaim::create([
            'event_id' => $event->id,
            'gift_id'  => $gift->id,
            'guest_id' => $guest->id,
            'quantity' => 1,
            'status'   => EventGiftClaim::STATUS_RESERVED,
        ]);

        $response = $this->postJson(
            route('events.gifts.unreserve', ['slug' => $event->slug, 'gift' => $gift->id]),
            [
                'invitation_code' => $guest->invitation_code,
            ]
        );

        $response->assertStatus(404);
    }

    /** @test */
    public function no_permite_liberar_un_regalo_que_el_invitado_no_tiene_reservado()
    {
        $event = Event::factory()->create([
            'slug'    => 'evento-unclaim-sin-reserva',
            'status'  => Event::STATUS_ACTIVE,
            'modules' => [
                'gifts' => true,
            ],
            'settings' => [
                'gifts_require_invitation_code'      => true,
                'gifts_allow_unclaim'                => true,
                'gifts_max_units_per_guest_per_gift' => 1,
            ],
        ]);

        $guest = Guest::factory()->create([
            'event_id'        => $event->id,
            'invitation_code' => 'SINRESERVA',
        ]);

        $gift = EventGift::create([
            'event_id'          => $event->id,
            'name'              => 'Regalo sin reserva',
            'quantity'          => 1,
            'quantity_reserved' => 0,
            'status'            => EventGift::STATUS_PENDING,
            'display_order'     => 1,
        ]);

        $response = $this->postJson(
            route('events.gifts.unreserve', ['slug' => $event->slug, 'gift' => $gift->id]),
            [
                'invitation_code' => $guest->invitation_code,
            ]
        );

        $response
            ->assertStatus(422)
            ->assertJsonFragment([
                'message' => 'No tienes este regalo apartado.',
            ]);
    }

    /** @test */
    public function no_permite_liberar_si_el_claim_esta_marcado_como_purchased()
    {
        $event = Event::factory()->create([
            'slug'    => 'evento-unclaim-comprado',
            'status'  => Event::STATUS_ACTIVE,
            'modules' => [
                'gifts' => true,
            ],
            'settings' => [
                'gifts_require_invitation_code'      => true,
                'gifts_allow_unclaim'                => true,
                'gifts_max_units_per_guest_per_gift' => 1,
            ],
        ]);

        $guest = Guest::factory()->create([
            'event_id'        => $event->id,
            'invitation_code' => 'COMPRADO-UNCLAIM',
        ]);

        $gift = EventGift::create([
            'event_id'          => $event->id,
            'name'              => 'Regalo comprado',
            'quantity'          => 1,
            'quantity_reserved' => 1,
            'status'            => EventGift::STATUS_PURCHASED,
            'display_order'     => 1,
        ]);

        EventGiftClaim::create([
            'event_id' => $event->id,
            'gift_id'  => $gift->id,
            'guest_id' => $guest->id,
            'quantity' => 1,
            'status'   => EventGiftClaim::STATUS_PURCHASED,
        ]);

        $response = $this->postJson(
            route('events.gifts.unreserve', ['slug' => $event->slug, 'gift' => $gift->id]),
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
}
