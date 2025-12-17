<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        $name = 'Evento ' . $this->faker->sentence(3);

        return [
            'type'       => $this->faker->randomElement(['wedding', 'xv', 'birthday', 'baby_shower']),
            'name'       => $name,
            'slug'       => Str::slug($name . '-' . $this->faker->unique()->numberBetween(1, 99999)),
            'status'     => Event::STATUS_ACTIVE,
            'event_date' => $this->faker->dateTimeBetween('+1 week', '+1 year'),
            'start_time' => '18:00:00',
            'end_time'   => '23:00:00',

            'theme_key'       => 'default',
            'primary_color'   => '#f472b6',
            'secondary_color' => '#0f172a',
            'accent_color'    => '#f9a8d4',
            'font_family'     => 'Playfair Display',

            // 🔒 Normalizado (sin legacy keys)
            'modules' => Event::normalizeModulesForStorage([]),

            'settings' => [
                'playlist_enabled'                   => true,
                'playlist_allow_guests_to_add_songs' => true,
                'playlist_max_songs_per_guest'       => 3,
                'playlist_max_votes_per_guest'       => 10,
                'public_show_song_author'            => true,
            ],

            'owner_name'  => $this->faker->name(),
            'owner_email' => $this->faker->safeEmail(),

            'auto_cleanup_after_days' => 60,
        ];
    }
}
