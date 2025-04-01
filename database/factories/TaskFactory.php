<?php

namespace Database\Factories;

use App\Enums\PriorityEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(3),
            'estimated_minutes' => $this->faker->numberBetween(10, 120),
            'completed_at' => $this->faker->optional()->dateTime(),
            'priority_id' => PriorityEnum::random(),
            'scheduled_day' => $this->faker->dateTime()->format('Y-m-d'),
        ];
    }
}
