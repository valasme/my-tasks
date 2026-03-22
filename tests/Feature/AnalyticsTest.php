<?php

namespace Tests\Feature;

use App\Models\HabitStreak;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    // ---------------------------------------------------------------
    // Authentication Guards
    // ---------------------------------------------------------------

    public function test_guest_cannot_access_analytics(): void
    {
        $response = $this->get(route('analytics.index'));

        $response->assertRedirect(route('login'));
    }

    // ---------------------------------------------------------------
    // Index
    // ---------------------------------------------------------------

    public function test_authenticated_user_can_view_analytics(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('analytics.index'));

        $response->assertOk();
        $response->assertSeeText('Analytics');
    }

    public function test_analytics_shows_zero_completion_ratio_when_no_tasks(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('analytics.index'));

        $response->assertOk();
        $response->assertSeeText('0%');
    }

    public function test_analytics_calculates_completion_ratio(): void
    {
        $user = User::factory()->create();

        Task::factory()->count(3)->for($user)->completed()->create();
        Task::factory()->for($user)->create(['status' => 'pending', 'due_date' => now()->addDay()->format('Y-m-d')]);

        $response = $this->actingAs($user)->get(route('analytics.index'));

        // 3 of 4 completed = 75%
        $response->assertOk();
        $response->assertSeeText('75%');
    }

    public function test_analytics_shows_total_and_completed_task_counts(): void
    {
        $user = User::factory()->create();

        Task::factory()->count(2)->for($user)->completed()->create();
        Task::factory()->for($user)->create(['status' => 'pending', 'due_date' => now()->addDay()->format('Y-m-d')]);

        $response = $this->actingAs($user)->get(route('analytics.index'));

        $response->assertOk();
        $response->assertSeeText('2');
        $response->assertSeeText('3');
    }

    public function test_analytics_shows_habit_streaks(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->recurringDaily()->for($user)->create(['title' => 'Morning Run']);

        HabitStreak::factory()->for($user)->for($task)->create([
            'current_streak' => 7,
        ]);

        $response = $this->actingAs($user)->get(route('analytics.index'));

        $response->assertOk();
        $response->assertSeeText('Morning Run');
        $response->assertSeeText('7');
    }

    public function test_analytics_only_shows_own_data(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Task::factory()->count(5)->for($otherUser)->completed()->create();

        $response = $this->actingAs($user)->get(route('analytics.index'));

        $response->assertOk();
        $response->assertSeeText('0%');
    }
}
