<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\WeeklyReview;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for generating {@see WeeklyReview} model instances in tests and seeders.
 *
 * @extends Factory<WeeklyReview>
 */
class WeeklyReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $weekStart = fake()->dateTimeBetween('-8 weeks', '-1 week');
        $weekEnd = (clone $weekStart)->modify('+6 days');
        $completed = fake()->numberBetween(5, 20);
        $missed = fake()->numberBetween(0, 5);

        return [
            'user_id' => User::factory(),
            'week_start' => $weekStart->format('Y-m-d'),
            'week_end' => $weekEnd->format('Y-m-d'),
            'tasks_completed' => $completed,
            'tasks_missed' => $missed,
            'tasks_created' => fake()->numberBetween($completed, $completed + 10),
            'summary' => fake()->optional(0.7)->paragraph(),
        ];
    }
}
