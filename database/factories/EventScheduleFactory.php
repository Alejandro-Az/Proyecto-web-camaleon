<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EventSchedule>
 */
class EventScheduleFactory extends Factory
{
    protected $model = EventSchedule::class;

    public function definition(): array
    {
        $startsAt = $this->faker->dateTimeBetween('+1 week', '+1 year');

        return [
            'event_id'       => Event::factory(),
            'title'          => $this->faker->sentence(4),
            'description'    => $this->faker->boolean(60) ? $this->faker->sentence() : null,
            'starts_at'      => $startsAt,
            'ends_at'        => $this->faker->boolean(70)
                ? (clone $startsAt)->modify('+1 hour')
                : null,
            'location_label' => $this->faker->boolean(50) ? $this->faker->streetAddress() : null,
            'location_type'  => $this->faker->randomElement(['ceremony', 'reception', 'other', null]),
            'display_order'  => $this->faker->numberBetween(1, 20),
            'is_enabled'     => true,
        ];
    }
}
