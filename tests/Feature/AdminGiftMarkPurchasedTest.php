<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventGift;
use App\Models\EventGiftClaim;
use App\Models\Guest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminGiftMarkPurchasedTest extends TestCase
{
    use RefreshDatabase;

    private function adminToken(): string
    {
        $admin = User::factory()->admin()->create();
        return auth('api')->login($admin);
    }

    private function makeEvent(): Event
    {
        return Event::factory()->create([
            'modules' => ['gifts' => true],
        ]);
    }

    // ─── autorización ─────────────────────────────────────────────────────────

    /** @test */
    public function sin_token_retorna_401(): void
    {
        $event = $this->makeEvent();
        $gift  = EventGift::create([
            'event_id'      => $event->id,
            'name'          => 'Regalo',
            'quantity'      => 1,
            'display_order' => 1,
        ]);

        $this->postJson(
            route('admin.events.gifts.markPurchased', ['event' => $event->id, 'gift' => $gift->id])
        )->assertStatus(401);
    }

    /** @test */
    public function con_token_client_retorna_403(): void
    {
        $client = User::factory()->client()->create();
        $token  = auth('api')->login($client);

        $event = $this->makeEvent();
        $gift  = EventGift::create([
            'event_id'      => $event->id,
            'name'          => 'Regalo',
            'quantity'      => 1,
            'display_order' => 1,
        ]);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson(
                route('admin.events.gifts.markPurchased', ['event' => $event->id, 'gift' => $gift->id])
            )->assertStatus(403);
    }

    // ─── lógica de negocio ────────────────────────────────────────────────────

    /** @test */
    public function admin_puede_marcar_regalo_como_comprado(): void
    {
        $token = $this->adminToken();
        $event = $this->makeEvent();

        $gift = EventGift::create([
            'event_id'          => $event->id,
            'name'              => 'Tostador',
            'quantity'          => 1,
            'quantity_reserved' => 1,
            'status'            => EventGift::STATUS_RESERVED,
            'display_order'     => 1,
        ]);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson(
                route('admin.events.gifts.markPurchased', ['event' => $event->id, 'gift' => $gift->id])
            )
            ->assertStatus(200)
            ->assertJsonFragment([
                'gift_id' => $gift->id,
                'status'  => EventGift::STATUS_PURCHASED,
            ]);

        $this->assertDatabaseHas('event_gifts', [
            'id'     => $gift->id,
            'status' => EventGift::STATUS_PURCHASED,
        ]);
    }

    /** @test */
    public function marcar_comprado_congela_claims_reservados(): void
    {
        $token = $this->adminToken();
        $event = $this->makeEvent();

        $guest = Guest::factory()->create(['event_id' => $event->id]);

        $gift = EventGift::create([
            'event_id'          => $event->id,
            'name'              => 'Vajilla',
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

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson(
                route('admin.events.gifts.markPurchased', ['event' => $event->id, 'gift' => $gift->id])
            )->assertStatus(200);

        $this->assertDatabaseHas('event_gift_claims', [
            'id'     => $claim->id,
            'status' => EventGiftClaim::STATUS_PURCHASED,
        ]);
    }

    /** @test */
    public function no_se_puede_marcar_comprado_regalo_de_otro_evento(): void
    {
        $token      = $this->adminToken();
        $event      = $this->makeEvent();
        $otherEvent = $this->makeEvent();

        $gift = EventGift::create([
            'event_id'      => $otherEvent->id,
            'name'          => 'Regalo ajeno',
            'quantity'      => 1,
            'display_order' => 1,
        ]);

        // Enviamos el ID del gift de otro evento a la ruta del primer evento
        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson(
                route('admin.events.gifts.markPurchased', ['event' => $event->id, 'gift' => $gift->id])
            )->assertStatus(404);
    }

    /** @test */
    public function modulo_gifts_apagado_retorna_404(): void
    {
        $token = $this->adminToken();
        $event = Event::factory()->create(['modules' => ['gifts' => false]]);

        $gift = EventGift::create([
            'event_id'      => $event->id,
            'name'          => 'Regalo',
            'quantity'      => 1,
            'display_order' => 1,
        ]);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson(
                route('admin.events.gifts.markPurchased', ['event' => $event->id, 'gift' => $gift->id])
            )->assertStatus(404);
    }

    /** @test */
    public function marcar_comprado_es_idempotente(): void
    {
        $token = $this->adminToken();
        $event = $this->makeEvent();

        $gift = EventGift::create([
            'event_id'      => $event->id,
            'name'          => 'Regalo ya comprado',
            'quantity'      => 1,
            'status'        => EventGift::STATUS_PURCHASED,
            'display_order' => 1,
        ]);

        // Segunda llamada sobre un regalo ya purchased
        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson(
                route('admin.events.gifts.markPurchased', ['event' => $event->id, 'gift' => $gift->id])
            )->assertStatus(200)
            ->assertJsonFragment(['status' => EventGift::STATUS_PURCHASED]);
    }
}
