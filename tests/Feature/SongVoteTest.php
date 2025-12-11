<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventSong;
use App\Models\Guest;
use App\Models\SongVote;
use Database\Seeders\DemoEventsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SongVoteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Cargamos los datos demo
        $this->seed(DemoEventsSeeder::class);
    }

    /** @test */
    public function invitado_puede_votar_y_quitar_su_voto_por_json()
    {
        // Arrange
        $event = Event::where('slug', 'boda-prueba-ana-luis')->firstOrFail();
        $guest = Guest::where('event_id', $event->id)->firstOrFail();
        $song  = EventSong::where('event_id', $event->id)->firstOrFail();

        $initialVotes = $song->votes_count;

        // Act: votar
        $voteResponse = $this->postJson(
            route('events.songs.vote', ['slug' => $event->slug, 'song' => $song->id]),
            [
                'invitation_code' => $guest->invitation_code,
            ]
        );

        $song->refresh();

        // Assert: voto creado
        $voteResponse
            ->assertStatus(200)
            ->assertJsonFragment([
                'has_voted' => true,
            ]);

        $this->assertDatabaseHas('song_votes', [
            'event_id' => $event->id,
            'song_id'  => $song->id,
            'guest_id' => $guest->id,
        ]);

        $this->assertEquals($initialVotes + 1, $song->votes_count);

        // Act: volver a mandar el voto (toggle, debe quitarse)
        $unvoteResponse = $this->postJson(
            route('events.songs.vote', ['slug' => $event->slug, 'song' => $song->id]),
            [
                'invitation_code' => $guest->invitation_code,
            ]
        );

        $song->refresh();

        // Assert: voto eliminado
        $unvoteResponse
            ->assertStatus(200)
            ->assertJsonFragment([
                'has_voted' => false,
            ]);

        $this->assertDatabaseMissing('song_votes', [
            'event_id' => $event->id,
            'song_id'  => $song->id,
            'guest_id' => $guest->id,
        ]);

        $this->assertEquals($initialVotes, $song->votes_count);
    }

    /** @test */
    public function respeta_el_limite_de_votos_por_invitado()
    {
        // Arrange
        $event = Event::where('slug', 'boda-prueba-ana-luis')->firstOrFail();
        $guest = Guest::where('event_id', $event->id)->firstOrFail();

        // Ajustamos el límite a 1 voto
        $settings = $event->settings;
        $settings['playlist_max_votes_per_guest'] = 1;
        $event->settings = $settings;
        $event->save();

        // Creamos dos canciones simples para este evento (además de las del seeder si las hubiera)
        $songA = EventSong::create([
            'event_id'    => $event->id,
            'title'       => 'Canción A',
            'artist'      => 'Artista A',
            'status'      => EventSong::STATUS_APPROVED,
            'votes_count' => 0,
        ]);

        $songB = EventSong::create([
            'event_id'    => $event->id,
            'title'       => 'Canción B',
            'artist'      => 'Artista B',
            'status'      => EventSong::STATUS_APPROVED,
            'votes_count' => 0,
        ]);

        // Act: votar por la canción A (ok)
        $firstVote = $this->postJson(
            route('events.songs.vote', ['slug' => $event->slug, 'song' => $songA->id]),
            [
                'invitation_code' => $guest->invitation_code,
            ]
        );

        // Segundo voto (canción B) debe fallar por límite
        $secondVote = $this->postJson(
            route('events.songs.vote', ['slug' => $event->slug, 'song' => $songB->id]),
            [
                'invitation_code' => $guest->invitation_code,
            ]
        );

        // Assert
        $firstVote
            ->assertStatus(200)
            ->assertJsonFragment(['has_voted' => true]);

        $secondVote
            ->assertStatus(422)
            ->assertJsonFragment([
                'message' => 'Ya ha usado todos sus votos disponibles para este evento.',
            ]);
    }

    /** @test */
    public function requiere_un_codigo_de_invitacion_valido_para_votar()
    {
        // Arrange
        $event = Event::where('slug', 'boda-prueba-ana-luis')->firstOrFail();
        $song  = EventSong::where('event_id', $event->id)->firstOrFail();

        // Act
        $response = $this->postJson(
            route('events.songs.vote', ['slug' => $event->slug, 'song' => $song->id]),
            [
                'invitation_code' => 'CODIGO-INVALIDO',
            ]
        );

        // Assert
        $response
            ->assertStatus(422)
            ->assertJsonFragment([
                'message' => 'No pudimos identificar su invitación. Use el enlace personal que recibió.',
            ]);
    }
}
