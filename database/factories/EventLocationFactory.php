<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories.Factory<\App\Models\EventLocation>
 */
class EventLocationFactory extends Factory
{
    /**
     * El modelo correspondiente.
     *
     * @var class-string<\App\Models\EventLocation>
     */
    protected $model = EventLocation::class;

    /**
     * Define el estado por defecto del modelo.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),

            'type'    => $this->faker->randomElement(['ceremony', 'reception']),
            'name'    => 'Lugar ' . $this->faker->word(),
            'address' => $this->faker->address(),
            'maps_url'=> 'https://maps.google.com',

            'display_order' => 1,
        ];
    }
}
