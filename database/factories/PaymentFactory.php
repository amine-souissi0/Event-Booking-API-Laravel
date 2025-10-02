<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'amount'     => $this->faker->randomFloat(2, 10, 800),
            'status'     => $this->faker->randomElement(['success','failed','refunded']),
        ];
    }
}
