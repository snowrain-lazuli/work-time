<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Application;
use App\Models\User;
use App\Models\Time;

class ApplicationFactory extends Factory
{
    protected $model = Application::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'time_id' => Time::factory(),
            'applicant_id' => User::factory(),
            'approver_id' => User::factory(),
            'day' => $this->faker->date(),
            'application_type' => $this->faker->randomElement(['通院のため遅刻', '遅延の為遅刻', '遅延の為']),
            'status' => $this->faker->randomElement([1, 2]),
        ];
    }
}