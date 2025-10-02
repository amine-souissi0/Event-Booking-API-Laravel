<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        return [
            'user_id'   => User::factory(),
            'ticket_id' => Ticket::factory(),
            'quantity'  => $this->faker->numberBetween(1, 5),
            'status'    => $this->faker->randomElement([
                Booking::STATUS_PENDING,
                Booking::STATUS_CONFIRMED,
                Booking::STATUS_CANCELLED,
            ]),
        ];
    }
}
