<?php

namespace Database\Factories;

use App\Models\HabitStreak;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for generating {@see HabitStreak} model instances in tests and seeders.
 *
 * @extends Factory<HabitStreak>
 */
class HabitStreakFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $currentStreak = fake()->numberBetween(0, 30);

        return [
            'user_id' => User::factory(),
            'task_id' => Task::factory(),
            'current_streak' => $currentStreak,
            'longest_streak' => fake()->numberBetween($currentStreak, $currentStreak + 20),
            'last_completed_date' => fake()->optional(0.8)->dateTimeBetween('-7 days', 'now'),
        ];
    }

    /**
     * Indicate the streak is currently active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes): array => [
            'current_streak' => fake()->numberBetween(1, 30),
            'last_completed_date' => now()->format('Y-m-d'),
        ]);
    }

    /**
     * Indicate the streak is broken.
     */
    public function broken(): static
    {
        return $this->state(fn (array $attributes): array => [
            'current_streak' => 0,
            'last_completed_date' => fake()->dateTimeBetween('-30 days', '-3 days'),
        ]);
    }
}
