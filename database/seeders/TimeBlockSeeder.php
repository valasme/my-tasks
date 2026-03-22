<?php

namespace Database\Seeders;

use App\Models\TimeBlock;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds time blocks for each user.
 */
class TimeBlockSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all(['id']);

        if ($users->isEmpty()) {
            $this->command->warn('No users found — skipping time block seeding.');

            return;
        }

        foreach ($users as $user) {
            TimeBlock::factory()->count(3)->for($user)->state(['date' => today()->format('Y-m-d')])->create();
            TimeBlock::factory()->count(2)->for($user)->create();
        }
    }
}
