<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserXp;
use App\Models\XpTransaction;
use Illuminate\Database\Seeder;

/**
 * Seeds XP records and transactions for each user.
 */
class XpSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all(['id']);

        if ($users->isEmpty()) {
            $this->command->warn('No users found — skipping XP seeding.');

            return;
        }

        foreach ($users as $user) {
            UserXp::factory()->create(['user_id' => $user->id]);
            XpTransaction::factory()->count(10)->create(['user_id' => $user->id]);
        }
    }
}
