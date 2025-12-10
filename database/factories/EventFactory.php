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
    /**
     * El nombre del modelo correspondiente.
     *
     * @var class-string<\App\Models\Event>
     */
    protected $model = Event::class;

    /**
     * Define el estado por defecto del modelo.
     *
     * @return array<string, mixed>
     */
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

            'modules' => [
                'gallery'                => true,
                'playlist_suggestions'   => true,
                'playlist_votes'         => true,
                'rsvp'                   => true,
                'public_attendance_list' => false,
                'dress_code'             => true,
                'gifts'                  => true,
                'guest_photos_upload'    => false,
                'romantic_phrases'       => true,
                'countdown'              => true,
                'map'                    => true,
                'schedule'               => true,
            ],
            'settings' => [
                'playlist_max_songs_per_guest' => 3,
                'playlist_max_votes_per_guest' => 10,
            ],

            'owner_name'  => $this->faker->name(),
            'owner_email' => $this->faker->safeEmail(),

            'auto_cleanup_after_days' => 60,
        ];
    }
}
