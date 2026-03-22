<?php

namespace Database\Factories;

use App\Models\TimeBlock;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for generating {@see TimeBlock} model instances in tests and seeders.
 *
 * @extends Factory<TimeBlock>
 */
class TimeBlockFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startHour = fake()->numberBetween(8, 16);
        $duration = fake()->randomElement([30, 60, 90, 120]);

        return [
            'user_id' => User::factory(),
            'task_id' => null,
            'title' => fake()->sentence(3),
            'date' => fake()->dateTimeBetween('now', '+7 days')->format('Y-m-d'),
            'start_time' => sprintf('%02d:00', $startHour),
            'end_time' => sprintf('%02d:%02d', $startHour + intdiv($duration, 60), $duration % 60),
            'estimated_minutes' => $duration,
        ];
    }
}
