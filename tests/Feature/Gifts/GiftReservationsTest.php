<?php

namespace Tests\Feature\Gifts;

use App\Models\Event;
use App\Models\EventGift;
use App\Models\EventGiftClaim;
use App\Models\Guest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GiftReservationsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function invitado_puede_reservar_multiples_unidades_cuando_setting_activo(): void
    {
        $event = Event::factory()->create([
            'modules' => ['gifts' => true],
            'settings' => [
                'gifts_require_invitation_code' => true,
                'gifts_allow_multi_unit_reserve' => true,
                'gifts_max_units_per_guest_per_gift' => 5,
            ],
        ]);

        $guest = Guest::factory()->create([
            'event_id' => $event->id,
            'invitation_code' => 'INV-123',
            'name' => 'Juan Invitado',
        ]);

        $gift = EventGift::factory()->create([
            'event_id' => $event->id,
            'quantity' => 10,
            'quantity_reserved' => 0,
            'status' => EventGift::STATUS_PENDING,
        ]);

        $response = $this->postJson(route('events.gifts.reserve', [
            'slug' => $event->slug,
            'gift' => $gift->id,
        ]), [
            'invitation_code' => $guest->invitation_code,
            'quantity' => 3,
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'gift_id' => $gift->id,
                'my_claim_quantity' => 3,
            ]);

        $gift->refresh();

        $this->assertSame(3, (int) $gift->quantity_reserved);
        $this->assertSame(7, (int) $gift->available_units);

        $this->assertDatabaseHas('event_gift_claims', [
            'event_id' => $event->id,
            'gift_id'  => $gift->id,
            'guest_id' => $guest->id,
            'quantity' => 3,
            'status'   => EventGiftClaim::STATUS_RESERVED,
        ]);
    }

    /** @test */
    public function invitado_no_puede_reservar_mas_que_max_units_per_guest_per_gift(): void
    {
        $event = Event::factory()->create([
            'modules' => ['gifts' => true],
            'settings' => [
                'gifts_require_invitation_code' => true,
                'gifts_allow_multi_unit_reserve' => true,
                'gifts_max_units_per_guest_per_gift' => 2,
            ],
        ]);

        $guest = Guest::factory()->create([
            'event_id' => $event->id,
            'invitation_code' => 'INV-456',
            'name' => 'María Invitada',
        ]);

        $gift = EventGift::factory()->create([
            'event_id' => $event->id,
            'quantity' => 10,
            'quantity_reserved' => 0,
            'status' => EventGift::STATUS_PENDING,
        ]);

        // Primero aparta 2 (llega al máximo)
        $this->postJson(route('events.gifts.reserve', [
            'slug' => $event->slug,
            'gift' => $gift->id,
        ]), [
            'invitation_code' => $guest->invitation_code,
            'quantity' => 2,
        ])->assertStatus(200)
          ->assertJsonFragment(['my_claim_quantity' => 2]);

        // Luego intenta apartar 1 más: debe fallar
        $response = $this->postJson(route('events.gifts.reserve', [
            'slug' => $event->slug,
            'gift' => $gift->id,
        ]), [
            'invitation_code' => $guest->invitation_code,
            'quantity' => 1,
        ]);

        $response->assertStatus(422)
            ->assertJsonFragment([
                'message' => 'Ya te comprometiste con este regalo.',
            ]);

        // Debe seguir en 2
        $gift->refresh();
        $this->assertSame(2, (int) $gift->quantity_reserved);

        $this->assertDatabaseHas('event_gift_claims', [
            'event_id' => $event->id,
            'gift_id'  => $gift->id,
            'guest_id' => $guest->id,
            'quantity' => 2,
            'status'   => EventGiftClaim::STATUS_RESERVED,
        ]);
    }
}
