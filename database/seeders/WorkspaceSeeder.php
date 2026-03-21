<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Seeder;

/**
 * Seeds the workspaces table with sample data for every user.
 */
class WorkspaceSeeder extends Seeder
{
    /**
     * Seed the workspaces table.
     */
    public function run(): void
    {
        $users = User::all(['id']);

        if ($users->isEmpty()) {
            $this->command->warn('No users found — skipping workspace seeding.');

            return;
        }

        $names = ['Engineering', 'Marketing', 'Design', 'Operations'];

        foreach ($users as $user) {
            foreach ($names as $name) {
                Workspace::factory()->for($user)->create(['name' => $name]);
            }
        }
    }
}
