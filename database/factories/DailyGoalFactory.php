<?php

namespace Database\Factories;

use App\Models\DailyGoal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for generating {@see DailyGoal} model instances in tests and seeders.
 *
 * @extends Factory<DailyGoal>
 */
class DailyGoalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $target = fake()->numberBetween(3, 10);

        return [
            'user_id' => User::factory(),
            'date' => fake()->dateTimeBetween('-7 days', 'now')->format('Y-m-d'),
            'target_count' => $target,
            'completed_count' => fake()->numberBetween(0, $target),
        ];
    }

    /**
     * Indicate the goal was met.
     */
    public function met(): static
    {
        return $this->state(function (array $attributes): array {
            $target = $attributes['target_count'] ?? 5;

            return [
                'completed_count' => $target,
            ];
        });
    }
}
