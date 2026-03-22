<?php

namespace Database\Seeders;

use App\Models\MoodLog;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds mood logs for each user.
 */
class MoodLogSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all(['id']);

        if ($users->isEmpty()) {
            $this->command->warn('No users found — skipping mood log seeding.');

            return;
        }

        foreach ($users as $user) {
            MoodLog::factory()->count(15)->create(['user_id' => $user->id]);
        }
    }
}
