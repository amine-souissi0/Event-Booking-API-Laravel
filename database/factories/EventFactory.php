<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        return [
            'title'       => $this->faker->catchPhrase(),
            'description' => $this->faker->paragraph(),
            'date'        => $this->faker->dateTimeBetween('+1 day', '+6 months'),
            'location'    => $this->faker->city(),
            'created_by'  => User::factory()->organizer(),
        ];
    }
}
