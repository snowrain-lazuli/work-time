<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use app\Models\User;

class TimeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $dateTime = $this->faker->dateTime();
        return [
            'user_id' => User::factory(),
            'date' => $this->faker->date(),
            'start_time' => $dateTime->format('Y-m-d H:i:s'),
            'end_time' => $dateTime->format('Y-m-d H:i:s'),
            'status' => $this->faker->randomElement(['working', 'completed', 'pending']),
        ];
    }
}