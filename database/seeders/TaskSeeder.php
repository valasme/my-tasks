<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds the tasks table with a diverse set of sample data for every user.
 *
 * Each user receives tasks spread across different statuses,
 * priorities, recurring schedules, and overdue states.
 */
class TaskSeeder extends Seeder
{
    /**
     * Seed the tasks table.
     *
     * Iterates over every existing user and batch-creates tasks using
     * {@see TaskFactory} states to cover a variety
     * of scenarios including schedule statuses.
     */
    public function run(): void
    {
        $users = User::all(['id']);

        if ($users->isEmpty()) {
            $this->command->warn('No users found — skipping task seeding.');

            return;
        }

        foreach ($users as $user) {
            Task::factory()->count(5)->for($user)->create();
            Task::factory()->count(3)->completed()->for($user)->create();
            Task::factory()->count(2)->completedLate()->for($user)->create();
            Task::factory()->count(2)->highPriority()->for($user)->create();
            Task::factory()->count(2)->urgent()->for($user)->create();
            Task::factory()->count(2)->recurringDaily()->for($user)->create();
            Task::factory()->count(2)->overdue()->for($user)->create();
        }
    }
}
