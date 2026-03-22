<?php

namespace Database\Factories;

use App\Models\PomodoroSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for generating {@see PomodoroSession} model instances in tests and seeders.
 *
 * @extends Factory<PomodoroSession>
 */
class PomodoroSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startedAt = fake()->dateTimeBetween('-7 days', 'now');

        return [
            'user_id' => User::factory(),
            'task_id' => null,
            'started_at' => $startedAt,
            'ended_at' => (clone $startedAt)->modify('+25 minutes'),
            'duration_minutes' => 25,
            'type' => 'work',
            'status' => 'completed',
        ];
    }

    /**
     * Indicate the session is currently active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes): array => [
            'started_at' => now(),
            'ended_at' => null,
            'status' => 'active',
        ]);
    }

    /**
     * Indicate the session is a break.
     */
    public function break(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => 'break',
            'duration_minutes' => 5,
        ]);
    }

    /**
     * Indicate the session was cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'cancelled',
        ]);
    }
}
