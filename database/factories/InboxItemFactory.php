<?php

namespace Database\Factories;

use App\Models\InboxItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for generating {@see InboxItem} model instances in tests and seeders.
 *
 * @extends Factory<InboxItem>
 */
class InboxItemFactory extends Factory
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
            'body' => fake()->sentence(8),
            'is_processed' => false,
            'task_id' => null,
            'workspace_id' => null,
        ];
    }

    /**
     * Indicate the item has been processed.
     */
    public function processed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_processed' => true,
        ]);
    }
}
