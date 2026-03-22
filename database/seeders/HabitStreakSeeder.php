<?php

namespace Database\Seeders;

use App\Models\HabitStreak;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds habit streaks for recurring daily tasks.
 */
class HabitStreakSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all(['id']);

        if ($users->isEmpty()) {
            $this->command->warn('No users found — skipping habit streak seeding.');

            return;
        }

        foreach ($users as $user) {
            $recurringTasks = Task::where('user_id', $user->id)
                ->where('is_recurring_daily', true)
                ->get();

            foreach ($recurringTasks as $task) {
                HabitStreak::factory()->active()->create([
                    'user_id' => $user->id,
                    'task_id' => $task->id,
                ]);
            }
        }
    }
}
