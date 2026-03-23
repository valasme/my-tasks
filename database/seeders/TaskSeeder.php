<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Seeder;

/**
 * Seeds the tasks table with diverse sample data for every user.
 *
 * Each user receives tasks spread across statuses, priorities,
 * recurring schedules, overdue states, and categories to make
 * the application feel realistic during development.
 */
class TaskSeeder extends Seeder
{
    /**
     * Seed the tasks table.
     *
     * Generates a balanced mix of task types for each existing user.
     * Warns and returns early if no users exist.
     */
    public function run(): void
    {
        $users = User::all(['id']);

        if ($users->isEmpty()) {
            $this->command->warn('No users found — skipping task seeding.');

            return;
        }

        foreach ($users as $user) {
            $workspaceIds = Workspace::where('user_id', $user->id)->pluck('id')->all();
            $randomWorkspace = fn (): array => ['workspace_id' => fake()->randomElement($workspaceIds ?: [null])];

            Task::factory()->count(5)->for($user)->state($randomWorkspace)->create();
            Task::factory()->count(3)->completed()->for($user)->state($randomWorkspace)->create();
            Task::factory()->count(2)->completedLate()->for($user)->state($randomWorkspace)->create();
            Task::factory()->count(2)->highPriority()->for($user)->state($randomWorkspace)->create();
            Task::factory()->count(2)->urgent()->for($user)->state($randomWorkspace)->create();
            Task::factory()->count(2)->recurringDaily()->for($user)->state($randomWorkspace)->create();
            Task::factory()->count(2)->overdue()->for($user)->state($randomWorkspace)->create();
            Task::factory()->count(3)->somedayMaybe()->for($user)->state($randomWorkspace)->create();
            Task::factory()->count(2)->withEstimate(60)->for($user)->state($randomWorkspace)->create();
        }
    }
}
