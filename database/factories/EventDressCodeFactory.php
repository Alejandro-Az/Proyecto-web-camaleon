<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventDressCode;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EventDressCode>
 */
class EventDressCodeFactory extends Factory
{
    protected $model = EventDressCode::class;

    public function definition(): array
    {
        return [
            'event_id'         => Event::factory(),
            'title'            => $this->faker->randomElement(['Formal', 'Cocktail', 'Casual', 'Gala', 'Black Tie']),
            'description'      => $this->faker->boolean(70) ? $this->faker->sentence() : null,
            'examples'         => $this->faker->boolean(40) ? $this->faker->sentence() : null,
            'notes'            => $this->faker->boolean(30) ? $this->faker->sentence() : null,
            'icon'             => $this->faker->boolean(30) ? $this->faker->word() : null,
            'example_photo_id' => null,
            'display_order'    => $this->faker->numberBetween(1, 10),
            'is_enabled'       => true,
        ];
    }
}
