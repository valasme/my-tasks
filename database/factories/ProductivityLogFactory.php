<?php

namespace Database\Factories;

use App\Models\ProductivityLog;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for generating {@see ProductivityLog} model instances in tests and seeders.
 *
 * @extends Factory<ProductivityLog>
 */
class ProductivityLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $completedAt = fake()->dateTimeBetween('-30 days', 'now');

        return [
            'user_id' => User::factory(),
            'task_id' => Task::factory(),
            'completed_at' => $completedAt,
            'day_of_week' => (int) $completedAt->format('w'),
            'hour_of_day' => (int) $completedAt->format('G'),
        ];
    }
}
