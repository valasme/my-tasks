<?php

namespace Database\Seeders;

use App\Models\ProductivityLog;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds productivity logs from completed tasks.
 */
class ProductivityLogSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all(['id']);

        if ($users->isEmpty()) {
            $this->command->warn('No users found — skipping productivity log seeding.');

            return;
        }

        foreach ($users as $user) {
            $completedTasks = Task::where('user_id', $user->id)
                ->where('status', 'completed')
                ->get();

            foreach ($completedTasks as $task) {
                ProductivityLog::factory()->create([
                    'user_id' => $user->id,
                    'task_id' => $task->id,
                ]);
            }
        }
    }
}
