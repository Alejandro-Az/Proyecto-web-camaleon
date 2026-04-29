<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ClientPanelEventUpdateTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function cliente_puede_actualizar_informacion_basica_del_evento(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $event = Event::factory()->create([
            'owner_user_id' => $client->id,
            'name' => 'Nombre Original',
            'event_date' => '2025-10-10',
        ]);

        $this->actingAs($client, 'client')
            ->putJson(route('client.events.update', $event), [
                'name' => 'Nombre Nuevo',
                'event_date' => '2025-12-25',
                'start_time' => '18:00',
            ])
            ->assertStatus(200)
            ->assertJson(['message' => 'Evento actualizado correctamente']);

        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'name' => 'Nombre Nuevo',
            'start_time' => '18:00:00',
        ]);

        // event_date cast to Carbon; check via model to avoid SQLite datetime format mismatch
        $this->assertEquals('2025-12-25', $event->fresh()->event_date->toDateString());
    }

    /** @test */
    public function cliente_no_puede_actualizar_evento_ajeno(): void
    {
        $clientA = User::factory()->create(['role' => 'client']);
        $clientB = User::factory()->create(['role' => 'client']);
        $eventB = Event::factory()->create(['owner_user_id' => $clientB->id]);

        $this->actingAs($clientA, 'client')
            ->putJson(route('client.events.update', $eventB), [
                'name' => 'Hacker Name',
            ])
            ->assertStatus(404);
    }

    /** @test */
    public function cliente_puede_activar_desactivar_modulos_permitidos(): void
    {
        // Mock config for plan
        config(['event_plans.plans.premium.modules' => ['rsvp', 'gifts']]);

        $client = User::factory()->create(['role' => 'client']);
        $event = Event::factory()->create([
            'owner_user_id' => $client->id,
            'plan_key' => 'premium',
            'modules' => ['rsvp' => true, 'gifts' => false],
        ]);

        $this->actingAs($client, 'client')
            ->putJson(route('client.events.update', $event), [
                'modules' => [
                    'rsvp' => false,
                    'gifts' => true,
                ],
            ])
            ->assertStatus(200);

        $event->refresh();
        $this->assertFalse($event->isModuleEnabled('rsvp'));
        $this->assertTrue($event->isModuleEnabled('gifts'));
    }

    /** @test */
    public function cliente_no_puede_activar_modulo_no_permitido_por_plan(): void
    {
        // Mock config: standard only allows RSVP, NOT gifts
        config(['event_plans.plans.standard.modules' => ['rsvp']]);

        $client = User::factory()->create(['role' => 'client']);
        $event = Event::factory()->create([
            'owner_user_id' => $client->id,
            'plan_key' => 'standard',
        ]);

        $this->actingAs($client, 'client')
            ->putJson(route('client.events.update', $event), [
                'modules' => [
                    'gifts' => true, // Forbidden
                    'rsvp' => true,  // Allowed
                ],
            ])
            ->assertStatus(200); // Controller handles it gracefully (fail-closed)

        $event->refresh();
        $this->assertFalse($event->isModuleEnabled('gifts'), 'Gifts module should remain disabled via plan gating');
        $this->assertTrue($event->isModuleEnabled('rsvp'));
    }

    /** @test */
    public function cliente_puede_actualizar_settings(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $event = Event::factory()->create(['owner_user_id' => $client->id]);

        $this->actingAs($client, 'client')
            ->putJson(route('client.events.update', $event), [
                'settings' => [
                    'playlist_max_songs_per_guest' => 5,
                    'playlist_max_votes_per_guest' => 10,
                    'guest_photos_max_per_guest' => 20,
                    'guest_photos_auto_approve' => true,
                    'gifts_hide_purchased_from_public' => true,
                ],
            ])
            ->assertStatus(200);

        $event->refresh();
        $this->assertEquals(5, $event->getSetting('playlist_max_songs_per_guest'));
        $this->assertEquals(10, $event->getSetting('playlist_max_votes_per_guest'));
        $this->assertEquals(20, $event->getSetting('guest_photos_max_per_guest'));
        $this->assertTrue($event->getSetting('guest_photos_auto_approve'));
        $this->assertTrue($event->getSetting('gifts_hide_purchased_from_public'));
    }

    /** @test */
    public function cliente_puede_subir_hero_image(): void
    {
        Storage::fake('public');

        $client = User::factory()->create(['role' => 'client']);
        $event = Event::factory()->create(['owner_user_id' => $client->id]);

        $file = UploadedFile::fake()->image('hero.jpg');

        $response = $this->actingAs($client, 'client')
            ->post(route('client.events.hero', $event), [
                'hero_image' => $file,
            ])
            ->assertStatus(200);

        $path = $response->json('path');
        $this->assertNotNull($path);
        Storage::disk('public')->assertExists($path);
    }
}
