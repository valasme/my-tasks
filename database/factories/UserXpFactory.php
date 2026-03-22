<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserXp;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for generating {@see UserXp} model instances in tests and seeders.
 *
 * @extends Factory<UserXp>
 */
class UserXpFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $totalXp = fake()->numberBetween(0, 5000);

        return [
            'user_id' => User::factory(),
            'total_xp' => $totalXp,
            'level' => (int) floor($totalXp / 100) + 1,
        ];
    }

    /**
     * Indicate the user has high XP.
     */
    public function highLevel(): static
    {
        return $this->state(fn (array $attributes): array => [
            'total_xp' => 5000,
            'level' => 51,
        ]);
    }
}
