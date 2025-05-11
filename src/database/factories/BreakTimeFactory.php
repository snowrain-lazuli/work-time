<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Time;

class BreakTimeFactory extends Factory
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
            'time_id' => Time::factory(),
            'start_time' => $dateTime->format('Y-m-d H:i:s'),
            'end_time' => $dateTime->format('Y-m-d H:i:s'),
        ];
    }
}