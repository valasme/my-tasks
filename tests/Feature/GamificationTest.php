<?php

namespace Tests\Feature;

use App\Models\DailyGoal;
use App\Models\User;
use App\Models\UserXp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GamificationTest extends TestCase
{
    use RefreshDatabase;

    // ---------------------------------------------------------------
    // Authentication Guards
    // ---------------------------------------------------------------

    public function test_guest_cannot_access_gamification_index(): void
    {
        $response = $this->get(route('gamification.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_set_daily_goal(): void
    {
        $response = $this->post(route('gamification.daily-goal'), [
            'target_count' => 5,
        ]);

        $response->assertRedirect(route('login'));
    }

    // ---------------------------------------------------------------
    // Index
    // ---------------------------------------------------------------

    public function test_authenticated_user_can_view_gamification_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('gamification.index'));

        $response->assertOk();
        $response->assertSeeText('Gamification');
    }

    public function test_gamification_shows_xp_and_level(): void
    {
        $user = User::factory()->create();
        UserXp::factory()->for($user)->create([
            'total_xp' => 350,
            'level' => 4,
        ]);

        $response = $this->actingAs($user)->get(route('gamification.index'));

        $response->assertOk();
        $response->assertSeeText('350');
        $response->assertSeeText('4');
    }

    public function test_gamification_shows_today_goal_when_set(): void
    {
        $user = User::factory()->create();
        DailyGoal::factory()->for($user)->create([
            'date' => today()->format('Y-m-d'),
            'target_count' => 8,
        ]);

        $response = $this->actingAs($user)->get(route('gamification.index'));

        $response->assertOk();
        $response->assertSeeText('8');
    }

    public function test_gamification_shows_weekly_goals(): void
    {
        $user = User::factory()->create();

        DailyGoal::factory()->for($user)->create([
            'date' => now()->subDays(3)->format('Y-m-d'),
            'target_count' => 5,
        ]);

        $response = $this->actingAs($user)->get(route('gamification.index'));

        $response->assertOk();
        $response->assertSeeText('5');
    }

    public function test_gamification_shows_no_xp_when_none_exists(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('gamification.index'));

        $response->assertOk();
    }

    // ---------------------------------------------------------------
    // Set Daily Goal — Store
    // ---------------------------------------------------------------

    public function test_user_can_create_daily_goal(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('gamification.daily-goal'), [
            'target_count' => 5,
        ]);

        $response->assertRedirect(route('gamification.index'));
        $response->assertSessionHas('success', 'Daily goal updated.');

        $goal = DailyGoal::where('user_id', $user->id)->firstOrFail();
        $this->assertEquals(5, $goal->target_count);
        $this->assertTrue($goal->date->isToday());
    }

    public function test_user_can_update_existing_daily_goal_for_today(): void
    {
        $user = User::factory()->create();
        DailyGoal::factory()->for($user)->create([
            'date' => today()->format('Y-m-d'),
            'target_count' => 3,
        ]);

        $this->actingAs($user)->post(route('gamification.daily-goal'), [
            'target_count' => 10,
        ]);

        $goal = DailyGoal::where('user_id', $user->id)->firstOrFail();
        $this->assertEquals(10, $goal->target_count);
        $this->assertTrue($goal->date->isToday());
        $this->assertDatabaseCount('daily_goals', 1);
    }

    public function test_daily_goal_requires_target_count(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('gamification.daily-goal'), []);

        $response->assertSessionHasErrors('target_count');
    }

    public function test_daily_goal_rejects_zero_target_count(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('gamification.daily-goal'), [
            'target_count' => 0,
        ]);

        $response->assertSessionHasErrors('target_count');
    }

    public function test_daily_goal_rejects_negative_target_count(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('gamification.daily-goal'), [
            'target_count' => -1,
        ]);

        $response->assertSessionHasErrors('target_count');
    }

    public function test_daily_goal_rejects_target_count_above_fifty(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('gamification.daily-goal'), [
            'target_count' => 51,
        ]);

        $response->assertSessionHasErrors('target_count');
    }

    public function test_daily_goal_accepts_target_count_at_max_fifty(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('gamification.daily-goal'), [
            'target_count' => 50,
        ]);

        $response->assertRedirect(route('gamification.index'));
        $response->assertSessionHasNoErrors();
    }

    public function test_daily_goal_accepts_target_count_of_one(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('gamification.daily-goal'), [
            'target_count' => 1,
        ]);

        $response->assertRedirect(route('gamification.index'));
        $response->assertSessionHasNoErrors();
    }

    public function test_daily_goal_is_assigned_to_authenticated_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('gamification.daily-goal'), [
            'target_count' => 5,
        ]);

        $this->assertDatabaseHas('daily_goals', [
            'user_id' => $user->id,
        ]);
    }
}
