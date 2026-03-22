<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\WeeklyReview;
use Illuminate\Database\Seeder;

/**
 * Seeds weekly reviews for each user.
 */
class WeeklyReviewSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all(['id']);

        if ($users->isEmpty()) {
            $this->command->warn('No users found — skipping weekly review seeding.');

            return;
        }

        foreach ($users as $user) {
            WeeklyReview::factory()->count(4)->create(['user_id' => $user->id]);
        }
    }
}
