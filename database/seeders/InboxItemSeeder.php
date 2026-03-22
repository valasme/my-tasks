<?php

namespace Database\Seeders;

use App\Models\InboxItem;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds inbox items for each user.
 */
class InboxItemSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all(['id']);

        if ($users->isEmpty()) {
            $this->command->warn('No users found — skipping inbox item seeding.');

            return;
        }

        foreach ($users as $user) {
            InboxItem::factory()->count(5)->for($user)->create();
            InboxItem::factory()->count(2)->processed()->for($user)->create();
        }
    }
}
