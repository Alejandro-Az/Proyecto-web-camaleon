<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventSong;
use App\Models\Guest;
use Database\Seeders\DemoEventsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SongSuggestionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Cargamos los eventos demo (boda, XV, invitados, canciones, etc.)
        $this->seed(DemoEventsSeeder::class);
    }

    /** @test */
    public function invitado_puede_sugerir_una_cancion_por_json()
    {
        // Arrange
        $event = Event::where('slug', 'boda-prueba-ana-luis')->firstOrFail();
        $guest = Guest::where('event_id', $event->id)->firstOrFail();

        // Act
        $response = $this->postJson(route('events.songs.store', ['slug' => $event->slug]), [
            'invitation_code'     => $guest->invitation_code,
            'title'               => 'Canción Nueva Test',
            'artist'              => 'Artista Demo',
            'url'                 => 'https://example.com/song-demo',
            'message_for_couple'  => 'Para probar la sugerencia.',
            'show_author'         => true,
        ]);

        // Assert
        $response
            ->assertStatus(200)
            ->assertJsonFragment([
                'title'  => 'Canción Nueva Test',
                'artist' => 'Artista Demo',
            ]);

        $this->assertDatabaseHas('event_songs', [
            'event_id' => $event->id,
            'title'    => 'Canción Nueva Test',
            'artist'   => 'Artista Demo',
        ]);
    }

    /** @test */
    public function no_permite_cancion_duplicada_por_titulo_y_artista()
    {
        // Arrange
        $event = Event::where('slug', 'boda-prueba-ana-luis')->firstOrFail();
        $guest = Guest::where('event_id', $event->id)->firstOrFail();

        // En el seeder ya existe la canción "Perfect" - "Ed Sheeran" para este evento
        $response = $this->postJson(route('events.songs.store', ['slug' => $event->slug]), [
            'invitation_code' => $guest->invitation_code,
            'title'           => 'Perfect',
            'artist'          => 'Ed Sheeran',
        ]);

        // Assert
        $response
            ->assertStatus(422)
            ->assertJson([
                'duplicated' => true,
            ])
            ->assertJsonFragment([
                'message' => 'Esa canción ya está en la lista, puedes votar por ella.',
            ]);
    }

    /** @test */
    public function respeta_el_limite_de_canciones_por_invitado()
    {
        // Arrange
        $event = Event::where('slug', 'boda-prueba-ana-luis')->firstOrFail();
        $guest = Guest::where('event_id', $event->id)->firstOrFail();

        // Reducimos el límite a 1 para hacer la prueba sencilla
        $settings = $event->settings;
        $settings['playlist_max_songs_per_guest'] = 1;
        $event->settings = $settings;
        $event->save();

        // Aseguramos que este invitado no tenga canciones propias previas
        EventSong::where('event_id', $event->id)
            ->where('suggested_by_guest_id', $guest->id)
            ->delete();

        // Act: primera sugerencia (debe pasar)
        $first = $this->postJson(route('events.songs.store', ['slug' => $event->slug]), [
            'invitation_code' => $guest->invitation_code,
            'title'           => 'Primera Canción',
            'artist'          => 'Artista 1',
        ]);

        // Segunda sugerencia (debe ser rechazada por límite)
        $second = $this->postJson(route('events.songs.store', ['slug' => $event->slug]), [
            'invitation_code' => $guest->invitation_code,
            'title'           => 'Segunda Canción',
            'artist'          => 'Artista 2',
        ]);

        // Assert
        $first->assertStatus(200);

        $second
            ->assertStatus(422)
            ->assertJsonFragment([
                'message' => 'Ya ha sugerido el número máximo de canciones permitido para este evento.',
            ]);
    }

    /** @test */
    public function requiere_un_codigo_de_invitacion_valido_para_sugerir()
    {
        // Arrange
        $event = Event::where('slug', 'boda-prueba-ana-luis')->firstOrFail();

        // Act
        $response = $this->postJson(route('events.songs.store', ['slug' => $event->slug]), [
            'invitation_code' => 'CODIGO-INVALIDO',
            'title'           => 'Canción Test',
        ]);

        // Assert
        $response
            ->assertStatus(422)
            ->assertJsonFragment([
                'message' => 'No pudimos identificar su invitación. Use el enlace personal que recibió.',
            ]);
    }
}
