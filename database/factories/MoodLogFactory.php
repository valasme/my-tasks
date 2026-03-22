<?php

namespace Database\Factories;

use App\Models\MoodLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for generating {@see MoodLog} model instances in tests and seeders.
 *
 * @extends Factory<MoodLog>
 */
class MoodLogFactory extends Factory
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
            'task_id' => null,
            'mood' => fake()->randomElement(MoodLog::MOODS),
            'note' => fake()->optional(0.5)->sentence(),
            'logged_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }
}
