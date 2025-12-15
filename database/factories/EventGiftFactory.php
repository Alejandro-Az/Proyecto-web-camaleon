<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventGift;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EventGift>
 */
class EventGiftFactory extends Factory
{
    protected $model = EventGift::class;

    public function definition(): array
    {
        return [
            'event_id'            => Event::factory(),
            'name'                => $this->faker->words(3, true),
            'description'         => $this->faker->boolean(60) ? $this->faker->sentence() : null,
            'store_label'         => $this->faker->boolean(50) ? $this->faker->randomElement(['Amazon', 'Liverpool', 'Mercado Libre']) : null,
            'url'                 => $this->faker->boolean(50) ? $this->faker->url() : null,

            // Inventario / estado
            'quantity'            => $this->faker->numberBetween(1, 10),
            'quantity_reserved'   => 0,
            'status'              => EventGift::STATUS_PENDING,

            // Orden / tracking opcional
            'display_order'       => $this->faker->numberBetween(1, 50),
            'claimed_by_guest_id' => null,
            'reserved_at'         => null,
        ];
    }
}
