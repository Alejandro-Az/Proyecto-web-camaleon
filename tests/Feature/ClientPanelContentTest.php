<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Event;
use App\Models\EventDressCode;
use App\Models\EventGift;
use App\Models\EventRomanticPhrase;
use App\Models\EventStory;
use App\Models\EventSchedule;
use App\Models\EventLocation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ClientPanelContentTest extends TestCase
{
    use RefreshDatabase;

    protected $client;
    protected $event;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create client and event
        $this->client = User::factory()->create(['role' => 'client']);
        $this->event = Event::factory()->create([
            'owner_user_id' => $this->client->id, 
        ]);
    }

    /** @test */
    public function client_can_view_content_dashboard()
    {
        $response = $this->actingAs($this->client, 'client')
            ->get(route('client.events.content.index', $this->event->id));

        file_put_contents('debug_output.html', $response->getContent());

        $response->assertStatus(200)
            ->assertSee('Contenido del Evento');
    }

    /** @test */
    public function client_can_create_dress_code()
    {
        $response = $this->actingAs($this->client, 'client')
            ->post(route('client.eventos.dress-codes.store', $this->event->id), [
                'title' => 'Formal Playa',
                'description' => 'Guayabera y Lino',
                'is_enabled' => true,
                'display_order' => 1
            ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('event_dress_codes', [
            'event_id' => $this->event->id,
            'title' => 'Formal Playa',
            'description' => 'Guayabera y Lino'
        ]);
    }

    /** @test */
    public function client_can_update_dress_code()
    {
        $dressCode = EventDressCode::create([
            'event_id' => $this->event->id,
            'title' => 'Old Title',
            'is_enabled' => true
        ]);

        $response = $this->actingAs($this->client, 'client')
            ->put(route('client.dress-codes.update', $dressCode->id), [
                'title' => 'New Title',
                'description' => 'Updated Desc',
                'is_enabled' => false
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('event_dress_codes', [
            'id' => $dressCode->id,
            'title' => 'New Title',
            'is_enabled' => 0
        ]);
    }

    /** @test */
    public function client_can_delete_dress_code()
    {
        $dressCode = EventDressCode::create([
            'event_id' => $this->event->id,
            'title' => 'To Delete',
        ]);

        $response = $this->actingAs($this->client, 'client')
            ->delete(route('client.dress-codes.destroy', $dressCode->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('event_dress_codes', ['id' => $dressCode->id]);
    }

    /** @test */
    public function client_cannot_modify_other_clients_content()
    {
        $otherClient = User::factory()->create(['role' => 'client']);
        $otherEvent = Event::factory()->create(['owner_user_id' => $otherClient->id]);
        $otherDressCode = EventDressCode::create(['event_id' => $otherEvent->id, 'title' => 'Other']);

        // Try to update other's dress code
        $this->actingAs($this->client, 'client')
            ->put(route('client.dress-codes.update', $otherDressCode->id), ['title' => 'Hacked'])
            ->assertStatus(403);
            
        // Try to store in other's event
        $this->actingAs($this->client, 'client')
            ->post(route('client.eventos.dress-codes.store', $otherEvent->id), ['title' => 'Hacked'])
            ->assertStatus(403);
    }
    
    /** @test */
    public function client_can_manage_romantic_phrases()
    {
        // Store
        $this->actingAs($this->client, 'client')
            ->post(route('client.eventos.phrases.store', $this->event->id), [
                'phrase' => 'Love is in the air',
                'author' => 'Unknown'
            ])
            ->assertRedirect();
            
        $this->assertDatabaseHas('event_romantic_phrases', ['phrase' => 'Love is in the air']);
        
        $phrase = EventRomanticPhrase::where('phrase', 'Love is in the air')->first();
        
        // Update
        $this->actingAs($this->client, 'client')
            ->put(route('client.phrases.update', $phrase->id), [
                'phrase' => 'Amor en el aire',
                'author' => 'Yo'
            ]);
            
        $this->assertDatabaseHas('event_romantic_phrases', ['phrase' => 'Amor en el aire']);
    }

    /** @test */
    public function client_can_manage_stories()
    {
        // Store
        $this->actingAs($this->client, 'client')
            ->post(route('client.eventos.stories.store', $this->event->id), [
                'title' => 'Our Story',
                'body' => 'Once upon a time...'
            ])
            ->assertRedirect();
            
        $this->assertDatabaseHas('event_stories', ['title' => 'Our Story']);
    }

    /** @test */
    public function client_can_manage_schedule()
    {
        // Store
        $this->actingAs($this->client, 'client')
            ->post(route('client.eventos.schedules.store', $this->event->id), [
                'title' => 'Ceremony',
                'starts_at' => '2025-10-10 14:00:00',
                'is_enabled' => true
            ])
            ->assertRedirect();
            
        $this->assertDatabaseHas('event_schedules', ['title' => 'Ceremony']);
    }

    /** @test */
    public function schedule_end_date_must_be_after_start_date()
    {
        $response = $this->actingAs($this->client, 'client')
            ->post(route('client.eventos.schedules.store', $this->event->id), [
                'title' => 'Invalid Time',
                'starts_at' => '2025-10-10 14:00:00',
                'ends_at' => '2025-10-10 13:00:00', // Before start
            ]);
            
        $response->assertSessionHasErrors('ends_at');
    }

    /** @test */
    public function client_can_manage_locations()
    {
        // Store
        $this->actingAs($this->client, 'client')
            ->post(route('client.eventos.locations.store', $this->event->id), [
                'name' => 'Grand Hotel',
                'type' => 'Reception',
                'maps_url' => 'https://maps.google.com/test'
            ])
            ->assertRedirect();
            
        $this->assertDatabaseHas('event_locations', ['name' => 'Grand Hotel']);
    }

    /** @test */
    public function client_can_manage_gifts()
    {
        $this->actingAs($this->client, 'client')
            ->post(route('client.eventos.gifts.store', $this->event->id), [
                'name' => 'Vajilla',
                'description' => 'Set de 24 piezas',
                'store_label' => 'Liverpool',
                'url' => 'https://example.com/gift',
                'quantity' => 2,
                'display_order' => 1,
            ])
            ->assertRedirect();

        $gift = EventGift::where('event_id', $this->event->id)->first();
        $this->assertNotNull($gift);

        $this->actingAs($this->client, 'client')
            ->put(route('client.gifts.update', $gift->id), [
                'name' => 'Vajilla Premium',
                'description' => 'Set de 36 piezas',
                'store_label' => 'Amazon',
                'url' => 'https://example.com/gift-2',
                'quantity' => 3,
                'display_order' => 2,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('event_gifts', [
            'id' => $gift->id,
            'name' => 'Vajilla Premium',
            'quantity' => 3,
        ]);

        $this->actingAs($this->client, 'client')
            ->delete(route('client.gifts.destroy', $gift->id))
            ->assertRedirect();

        $this->assertSoftDeleted('event_gifts', ['id' => $gift->id]);
    }
}
