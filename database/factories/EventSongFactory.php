<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventSong;
use App\Models\Guest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EventSong>
 */
class EventSongFactory extends Factory
{
    protected $model = EventSong::class;

    public function definition(): array
    {
        return [
            'event_id'             => Event::factory(),
            'title'                => $this->faker->sentence(3),
            'artist'               => $this->faker->name(),
            'url'                  => $this->faker->boolean(60) ? $this->faker->url() : null,
            'message_for_couple'   => $this->faker->boolean(40) ? $this->faker->sentence() : null,
            'suggested_by_guest_id'=> null,
            'show_author'          => $this->faker->boolean(80),
            'status'               => EventSong::STATUS_PENDING,
            'votes_count'          => 0,
        ];
    }
}
