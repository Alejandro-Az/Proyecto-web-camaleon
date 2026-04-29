<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventGift;
use App\Models\EventGiftClaim;
use App\Models\Guest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GiftSummaryTest extends TestCase
{
    use RefreshDatabase;

    // ─── summary ──────────────────────────────────────────────────────────────

    /** @test */
    public function summary_retorna_estructura_correcta_con_modulo_activo(): void
    {
        $event = Event::factory()->create([
            'slug'     => 'evento-summary',
            'status'   => Event::STATUS_ACTIVE,
            'modules'  => ['gifts' => true],
            'settings' => ['gifts_require_invitation_code' => false],
        ]);

        EventGift::create([
            'event_id'          => $event->id,
            'name'              => 'Cafetera',
            'quantity'          => 3,
            'quantity_reserved' => 1,
            'status'            => 'pending',
            'display_order'     => 1,
        ]);

        $this->getJson("/eventos/{$event->slug}/regalos/resumen")
            ->assertStatus(200)
            ->assertJsonStructure([
                'event_id',
                'gifts' => [['gift_id', 'status', 'quantity', 'quantity_reserved', 'available_units', 'my_claim_quantity']],
            ])
            ->assertJsonFragment(['available_units' => 2]);
    }

    /** @test */
    public function summary_retorna_404_cuando_modulo_gifts_esta_apagado(): void
    {
        $event = Event::factory()->create([
            'slug'    => 'evento-summary-off',
            'status'  => Event::STATUS_ACTIVE,
            'modules' => ['gifts' => false],
        ]);

        $this->getJson("/eventos/{$event->slug}/regalos/resumen")
            ->assertStatus(404);
    }

    /** @test */
    public function summary_incluye_mi_reserva_cuando_codigo_valido(): void
    {
        $event = Event::factory()->create([
            'slug'     => 'evento-summary-code',
            'status'   => Event::STATUS_ACTIVE,
            'modules'  => ['gifts' => true],
            'settings' => ['gifts_require_invitation_code' => true],
        ]);

        $guest = Guest::factory()->create([
            'event_id'        => $event->id,
            'invitation_code' => 'SUMMARYCODE1',
        ]);

        $gift = EventGift::create([
            'event_id'          => $event->id,
            'name'              => 'Juego de sábanas',
            'quantity'          => 2,
            'quantity_reserved' => 1,
            'status'            => 'pending',
            'display_order'     => 1,
        ]);

        EventGiftClaim::create([
            'event_id' => $event->id,
            'gift_id'  => $gift->id,
            'guest_id' => $guest->id,
            'quantity' => 1,
            'status'   => EventGiftClaim::STATUS_RESERVED,
        ]);

        $this->getJson("/eventos/{$event->slug}/regalos/resumen?invitation_code={$guest->invitation_code}")
            ->assertStatus(200)
            ->assertJsonFragment(['my_claim_quantity' => 1]);
    }

    // ─── mis-reservas ─────────────────────────────────────────────────────────

    /** @test */
    public function mis_reservas_retorna_claims_del_invitado(): void
    {
        $event = Event::factory()->create([
            'slug'     => 'evento-mis-reservas',
            'status'   => Event::STATUS_ACTIVE,
            'modules'  => ['gifts' => true],
            'settings' => ['gifts_require_invitation_code' => true],
        ]);

        $guest = Guest::factory()->create([
            'event_id'        => $event->id,
            'invitation_code' => 'MISRESERVAS1',
        ]);

        $gift = EventGift::create([
            'event_id'          => $event->id,
            'name'              => 'Portarretratos',
            'quantity'          => 1,
            'quantity_reserved' => 1,
            'status'            => 'pending',
            'display_order'     => 1,
        ]);

        EventGiftClaim::create([
            'event_id' => $event->id,
            'gift_id'  => $gift->id,
            'guest_id' => $guest->id,
            'quantity' => 1,
            'status'   => EventGiftClaim::STATUS_RESERVED,
        ]);

        $this->getJson("/eventos/{$event->slug}/regalos/mis-reservas?invitation_code={$guest->invitation_code}")
            ->assertStatus(200);
    }

    /** @test */
    public function mis_reservas_retorna_404_cuando_modulo_gifts_esta_apagado(): void
    {
        $event = Event::factory()->create([
            'slug'    => 'evento-mis-reservas-off',
            'status'  => Event::STATUS_ACTIVE,
            'modules' => ['gifts' => false],
        ]);

        $this->getJson("/eventos/{$event->slug}/regalos/mis-reservas")
            ->assertStatus(404);
    }
}
