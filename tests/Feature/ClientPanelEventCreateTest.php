<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ClientPanelEventCreateTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function client_can_view_create_event_form(): void
    {
        $client = User::factory()->create(['role' => 'client']);

        $this->actingAs($client, 'client')
            ->get(route('client.events.create'))
            ->assertStatus(200)
            ->assertSee('Crear nuevo evento');
    }

    /** @test */
    public function client_can_create_event(): void
    {
        $client = User::factory()->create(['role' => 'client']);

        $response = $this->actingAs($client, 'client')
            ->post(route('client.events.store'), [
                'name' => 'Boda Ana y Luis',
                'type' => 'wedding',
                'event_date' => Carbon::now()->addDays(30)->toDateString(),
                'start_time' => '18:00',
                'plan_key' => 'premium',
            ]);

        $created = Event::where('owner_user_id', $client->id)
            ->where('name', 'Boda Ana y Luis')
            ->first();

        $this->assertNotNull($created);
        $response->assertRedirect(route('client.events.show', $created->id));

        $this->assertDatabaseHas('events', [
            'id' => $created->id,
            'owner_user_id' => $client->id,
            'type' => 'wedding',
            'plan_key' => 'premium',
            'status' => Event::STATUS_ACTIVE,
        ]);
    }
}
