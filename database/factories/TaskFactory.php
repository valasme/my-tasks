<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for generating {@see Task} model instances in tests and seeders.
 *
 * The default state creates a non-recurring task with a random status,
 * priority, and due date within the next 30 days.
 *
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
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
            'title' => fake()->sentence(4),
            'description' => fake()->optional(0.7)->paragraph(),
            'status' => fake()->randomElement(Task::STATUSES),
            'priority' => fake()->randomElement(Task::PRIORITIES),
            'due_date' => fake()->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
            'is_recurring_daily' => false,
            'recurring_times' => null,
            'estimated_minutes' => fake()->optional(0.5)->numberBetween(5, 240),
        ];
    }

    /** Create a completed task (on time — due date in the future). */
    public function completed(): static
    {
        return $this->state(fn (): array => [
            'status' => 'completed',
            'due_date' => fake()->dateTimeBetween('+1 day', '+30 days')->format('Y-m-d'),
            'completed_at' => now(),
        ]);
    }

    /** Create a task that was completed after its due date. */
    public function completedLate(): static
    {
        return $this->state(function (): array {
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

    /** Create a high-priority task. */
    public function highPriority(): static
    {
        return $this->state(fn (): array => ['priority' => 'high']);
    }

    /** Create an urgent-priority task. */
    public function urgent(): static
    {
        return $this->state(fn (): array => ['priority' => 'urgent']);
    }

    /**
     * Create a recurring daily task with two random time slots.
     *
     * Clears the due date and forces the status to "pending".
     */
    public function recurringDaily(): static
    {
        return $this->state(function (): array {
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

    /** Create an overdue task (past due date, still pending). */
    public function overdue(): static
    {
        return $this->state(fn (): array => [
            'due_date' => fake()->dateTimeBetween('-30 days', '-1 day')->format('Y-m-d'),
            'status' => 'pending',
        ]);
    }

    /** Create a "Someday / Maybe" item with no due date. */
    public function somedayMaybe(): static
    {
        return $this->state(fn (): array => [
            'category' => 'someday_maybe',
            'status' => 'pending',
            'due_date' => null,
        ]);
    }

    /** Create a task with a specific estimated duration in minutes. */
    public function withEstimate(int $minutes): static
    {
        return $this->state(fn (): array => ['estimated_minutes' => $minutes]);
    }
}
