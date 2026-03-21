<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for generating {@see Task} model instances in tests and seeders.
 *
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * Creates a pending or in-progress task with a random priority and
     * a due date within the next 30 days.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->optional(0.7)->paragraph(),
            'status' => fake()->randomElement(Task::STATUSES),
            'priority' => fake()->randomElement(Task::PRIORITIES),
            'due_date' => fake()->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
            'is_recurring_daily' => false,
            'recurring_times' => null,
        ];
    }

    /**
     * Indicate the task is completed (on time — due date in the future).
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'completed',
            'due_date' => fake()->dateTimeBetween('+1 day', '+30 days')->format('Y-m-d'),
            'completed_at' => now(),
        ]);
    }

    /**
     * Indicate the task was completed late (after the due date passed).
     */
    public function completedLate(): static
    {
        return $this->state(function (array $attributes): array {
            $dueDate = fake()->dateTimeBetween('-30 days', '-3 days');

            return [
                'status' => 'completed',
                'due_date' => $dueDate->format('Y-m-d'),
                'completed_at' => fake()->dateTimeBetween(
                    (clone $dueDate)->modify('+1 day'),
                    'now',
                ),
            ];
        });
    }

    /**
     * Indicate the task has high priority.
     */
    public function highPriority(): static
    {
        return $this->state(fn (array $attributes): array => [
            'priority' => 'high',
        ]);
    }

    /**
     * Indicate the task is urgent.
     */
    public function urgent(): static
    {
        return $this->state(fn (array $attributes): array => [
            'priority' => 'urgent',
        ]);
    }

    /**
     * Indicate the task is a recurring daily task.
     *
     * Clears the due date and forces the status to "pending".
     */
    public function recurringDaily(): static
    {
        return $this->state(function (array $attributes): array {
            $first = sprintf('%02d:%02d', fake()->numberBetween(6, 11), fake()->numberBetween(0, 59));
            $second = sprintf('%02d:%02d', fake()->numberBetween(12, 22), fake()->numberBetween(0, 59));

            return [
                'is_recurring_daily' => true,
                'recurring_times' => [$first, $second],
                'due_date' => null,
                'status' => 'pending',
            ];
        });
    }

    /**
     * Indicate the task is overdue (past due date, still pending).
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes): array => [
            'due_date' => fake()->dateTimeBetween('-30 days', '-1 day')->format('Y-m-d'),
            'status' => 'pending',
        ]);
    }
}
