<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventRomanticPhrase;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EventRomanticPhrase>
 */
class EventRomanticPhraseFactory extends Factory
{
    protected $model = EventRomanticPhrase::class;

    public function definition(): array
    {
        return [
            'event_id'      => Event::factory(),
            'phrase'        => $this->faker->sentence(10),
            'author'        => $this->faker->boolean(60) ? $this->faker->name() : null,
            'display_order' => $this->faker->numberBetween(1, 20),
            'is_enabled'    => true,
        ];
    }
}
