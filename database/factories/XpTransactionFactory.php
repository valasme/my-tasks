<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\XpTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for generating {@see XpTransaction} model instances in tests and seeders.
 *
 * @extends Factory<XpTransaction>
 */
class XpTransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'points' => fake()->numberBetween(5, 50),
            'reason' => fake()->randomElement(['task_completed', 'streak_bonus', 'daily_goal_met']),
        ];
    }
}
