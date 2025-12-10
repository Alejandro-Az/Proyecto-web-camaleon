<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Guest;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Guest>
 */
class GuestFactory extends Factory
{
    /**
     * El modelo correspondiente.
     *
     * @var class-string<\App\Models\Guest>
     */
    protected $model = Guest::class;

    /**
     * Define el estado por defecto del modelo.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id'         => Event::factory(),
            'name'             => $this->faker->name(),
            'email'            => $this->faker->safeEmail(),
            'phone'            => $this->faker->phoneNumber(),
            'invitation_code'  => strtoupper(Str::random(8)),
            'invited_seats'    => 2,
            'rsvp_status'      => Guest::RSVP_PENDING,
            'rsvp_message'     => null,
            'rsvp_public'      => false,
            'guests_confirmed' => null,
            'show_in_public_list' => false,
            'checked_in_at'    => null,
        ];
    }
}
