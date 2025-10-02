<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        return [
            'type'     => $this->faker->randomElement(['VIP','Standard','Early Bird']),
            'price'    => $this->faker->randomFloat(2, 10, 300),
            'quantity' => $this->faker->numberBetween(50, 300),
            'event_id' => Event::factory(),
        ];
    }
}
