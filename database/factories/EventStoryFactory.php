<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventStory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EventStory>
 */
class EventStoryFactory extends Factory
{
    protected $model = EventStory::class;

    public function definition(): array
    {
        return [
            'event_id'         => Event::factory(),
            'title'            => $this->faker->sentence(4),
            'subtitle'         => $this->faker->boolean(50) ? $this->faker->sentence(6) : null,
            'body'             => $this->faker->paragraphs(2, true),
            'example_photo_id' => null,
            'display_order'    => $this->faker->numberBetween(1, 20),
            'is_enabled'       => true,
        ];
    }
}
