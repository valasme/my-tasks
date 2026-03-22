<?php

namespace Database\Seeders;

use App\Models\DailyGoal;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds daily goals for each user.
 */
class DailyGoalSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all(['id']);

        if ($users->isEmpty()) {
            $this->command->warn('No users found — skipping daily goal seeding.');

            return;
        }

        foreach ($users as $user) {
            // Goals over the past 7 days
            for ($i = 6; $i >= 0; $i--) {
                DailyGoal::factory()->create([
                    'user_id' => $user->id,
                    'date' => now()->subDays($i)->format('Y-m-d'),
                ]);
            }
        }
    }
}
