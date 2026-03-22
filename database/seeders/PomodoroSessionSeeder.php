<?php

namespace Database\Seeders;

use App\Models\PomodoroSession;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds pomodoro sessions for each user.
 */
class PomodoroSessionSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all(['id']);

        if ($users->isEmpty()) {
            $this->command->warn('No users found — skipping pomodoro session seeding.');

            return;
        }

        foreach ($users as $user) {
            PomodoroSession::factory()->count(8)->for($user)->create();
            PomodoroSession::factory()->count(2)->break()->for($user)->create();
        }
    }
}
