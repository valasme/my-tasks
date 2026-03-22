<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WeeklyReview;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WeeklyReviewTest extends TestCase
{
    use RefreshDatabase;

    // ---------------------------------------------------------------
    // Authentication Guards
    // ---------------------------------------------------------------

    public function test_guest_cannot_access_weekly_reviews_index(): void
    {
        $response = $this->get(route('weekly-reviews.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_access_weekly_reviews_create(): void
    {
        $response = $this->get(route('weekly-reviews.create'));

        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_store_weekly_review(): void
    {
        $response = $this->post(route('weekly-reviews.store'), [
            'week_start' => '2025-01-06',
            'week_end' => '2025-01-12',
            'tasks_completed' => 10,
            'tasks_created' => 15,
            'tasks_missed' => 2,
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_view_weekly_review(): void
    {
        $review = WeeklyReview::factory()->create();

        $response = $this->get(route('weekly-reviews.show', $review));

        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_edit_weekly_review(): void
    {
        $review = WeeklyReview::factory()->create();

        $response = $this->get(route('weekly-reviews.edit', $review));

        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_update_weekly_review(): void
    {
        $review = WeeklyReview::factory()->create();

        $response = $this->put(route('weekly-reviews.update', $review), [
            'week_start' => '2025-01-06',
            'week_end' => '2025-01-12',
            'tasks_completed' => 5,
            'tasks_created' => 8,
            'tasks_missed' => 1,
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_delete_weekly_review(): void
    {
        $review = WeeklyReview::factory()->create();

        $response = $this->delete(route('weekly-reviews.destroy', $review));

        $response->assertRedirect(route('login'));
    }

    // ---------------------------------------------------------------
    // Authorization — Users cannot access other users' reviews
    // ---------------------------------------------------------------

    public function test_user_cannot_view_another_users_review(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $review = WeeklyReview::factory()->for($otherUser)->create();

        $response = $this->actingAs($user)->get(route('weekly-reviews.show', $review));

        $response->assertForbidden();
    }

    public function test_user_cannot_edit_another_users_review(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $review = WeeklyReview::factory()->for($otherUser)->create();

        $response = $this->actingAs($user)->get(route('weekly-reviews.edit', $review));

        $response->assertForbidden();
    }

    public function test_user_cannot_update_another_users_review(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $review = WeeklyReview::factory()->for($otherUser)->create();

        $response = $this->actingAs($user)->put(route('weekly-reviews.update', $review), [
            'week_start' => '2025-01-06',
            'week_end' => '2025-01-12',
            'tasks_completed' => 5,
            'tasks_created' => 8,
            'tasks_missed' => 1,
        ]);

        $response->assertForbidden();
    }

    public function test_user_cannot_delete_another_users_review(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $review = WeeklyReview::factory()->for($otherUser)->create();

        $response = $this->actingAs($user)->delete(route('weekly-reviews.destroy', $review));

        $response->assertForbidden();
    }

    // ---------------------------------------------------------------
    // Index
    // ---------------------------------------------------------------

    public function test_authenticated_user_can_view_weekly_reviews_index(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('weekly-reviews.index'));

        $response->assertOk();
        $response->assertSeeText('Weekly Reviews');
    }

    public function test_index_only_shows_own_reviews(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        WeeklyReview::factory()->for($user)->create([
            'week_start' => '2025-01-06',
            'week_end' => '2025-01-12',
        ]);
        WeeklyReview::factory()->for($otherUser)->create([
            'week_start' => '2024-12-30',
            'week_end' => '2025-01-05',
        ]);

        $response = $this->actingAs($user)->get(route('weekly-reviews.index'));

        $response->assertOk();
        $response->assertSeeText('Jan 6');
        $response->assertDontSeeText('Dec 30');
    }

    public function test_index_paginates_reviews(): void
    {
        $user = User::factory()->create();
        WeeklyReview::factory()->count(15)->for($user)->create();

        $response = $this->actingAs($user)->get(route('weekly-reviews.index'));

        $response->assertOk();
    }

    // ---------------------------------------------------------------
    // Create
    // ---------------------------------------------------------------

    public function test_user_can_view_weekly_review_create_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('weekly-reviews.create'));

        $response->assertOk();
    }

    // ---------------------------------------------------------------
    // Store
    // ---------------------------------------------------------------

    public function test_user_can_create_weekly_review(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('weekly-reviews.store'), [
            'week_start' => '2025-01-06',
            'week_end' => '2025-01-12',
            'tasks_completed' => 12,
            'tasks_created' => 15,
            'tasks_missed' => 3,
            'summary' => 'Productive week!',
        ]);

        $response->assertRedirect(route('weekly-reviews.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('weekly_reviews', [
            'user_id' => $user->id,
            'tasks_completed' => 12,
            'tasks_created' => 15,
            'tasks_missed' => 3,
            'summary' => 'Productive week!',
        ]);
    }

    public function test_weekly_review_assigned_to_authenticated_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('weekly-reviews.store'), [
            'week_start' => '2025-01-06',
            'week_end' => '2025-01-12',
            'tasks_completed' => 5,
            'tasks_created' => 8,
            'tasks_missed' => 1,
        ]);

        $this->assertDatabaseHas('weekly_reviews', [
            'user_id' => $user->id,
        ]);
    }

    public function test_store_requires_week_start(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('weekly-reviews.store'), [
            'week_end' => '2025-01-12',
            'tasks_completed' => 5,
            'tasks_created' => 8,
            'tasks_missed' => 1,
        ]);

        $response->assertSessionHasErrors('week_start');
    }

    public function test_store_requires_week_end(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('weekly-reviews.store'), [
            'week_start' => '2025-01-06',
            'tasks_completed' => 5,
            'tasks_created' => 8,
            'tasks_missed' => 1,
        ]);

        $response->assertSessionHasErrors('week_end');
    }

    public function test_store_rejects_week_end_before_week_start(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('weekly-reviews.store'), [
            'week_start' => '2025-01-12',
            'week_end' => '2025-01-06',
            'tasks_completed' => 5,
            'tasks_created' => 8,
            'tasks_missed' => 1,
        ]);

        $response->assertSessionHasErrors('week_end');
    }

    public function test_store_accepts_week_end_equal_to_week_start(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('weekly-reviews.store'), [
            'week_start' => '2025-01-06',
            'week_end' => '2025-01-06',
            'tasks_completed' => 0,
            'tasks_created' => 0,
            'tasks_missed' => 0,
        ]);

        $response->assertRedirect(route('weekly-reviews.index'));
        $response->assertSessionHasNoErrors();
    }

    public function test_store_requires_tasks_completed(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('weekly-reviews.store'), [
            'week_start' => '2025-01-06',
            'week_end' => '2025-01-12',
            'tasks_created' => 8,
            'tasks_missed' => 1,
        ]);

        $response->assertSessionHasErrors('tasks_completed');
    }

    public function test_store_requires_tasks_created(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('weekly-reviews.store'), [
            'week_start' => '2025-01-06',
            'week_end' => '2025-01-12',
            'tasks_completed' => 5,
            'tasks_missed' => 1,
        ]);

        $response->assertSessionHasErrors('tasks_created');
    }

    public function test_store_requires_tasks_missed(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('weekly-reviews.store'), [
            'week_start' => '2025-01-06',
            'week_end' => '2025-01-12',
            'tasks_completed' => 5,
            'tasks_created' => 8,
        ]);

        $response->assertSessionHasErrors('tasks_missed');
    }

    public function test_store_rejects_negative_tasks_completed(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('weekly-reviews.store'), [
            'week_start' => '2025-01-06',
            'week_end' => '2025-01-12',
            'tasks_completed' => -1,
            'tasks_created' => 8,
            'tasks_missed' => 1,
        ]);

        $response->assertSessionHasErrors('tasks_completed');
    }

    public function test_store_rejects_negative_tasks_created(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('weekly-reviews.store'), [
            'week_start' => '2025-01-06',
            'week_end' => '2025-01-12',
            'tasks_completed' => 5,
            'tasks_created' => -1,
            'tasks_missed' => 1,
        ]);

        $response->assertSessionHasErrors('tasks_created');
    }

    public function test_store_rejects_negative_tasks_missed(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('weekly-reviews.store'), [
            'week_start' => '2025-01-06',
            'week_end' => '2025-01-12',
            'tasks_completed' => 5,
            'tasks_created' => 8,
            'tasks_missed' => -1,
        ]);

        $response->assertSessionHasErrors('tasks_missed');
    }

    public function test_store_accepts_zero_for_all_counts(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('weekly-reviews.store'), [
            'week_start' => '2025-01-06',
            'week_end' => '2025-01-12',
            'tasks_completed' => 0,
            'tasks_created' => 0,
            'tasks_missed' => 0,
        ]);

        $response->assertRedirect(route('weekly-reviews.index'));
        $response->assertSessionHasNoErrors();
    }

    public function test_store_summary_is_optional(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('weekly-reviews.store'), [
            'week_start' => '2025-01-06',
            'week_end' => '2025-01-12',
            'tasks_completed' => 5,
            'tasks_created' => 8,
            'tasks_missed' => 1,
        ]);

        $response->assertRedirect(route('weekly-reviews.index'));
        $response->assertSessionHasNoErrors();
    }

    public function test_store_rejects_summary_exceeding_5000_chars(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('weekly-reviews.store'), [
            'week_start' => '2025-01-06',
            'week_end' => '2025-01-12',
            'tasks_completed' => 5,
            'tasks_created' => 8,
            'tasks_missed' => 1,
            'summary' => str_repeat('a', 5001),
        ]);

        $response->assertSessionHasErrors('summary');
    }

    // ---------------------------------------------------------------
    // Show
    // ---------------------------------------------------------------

    public function test_user_can_view_their_weekly_review(): void
    {
        $user = User::factory()->create();
        $review = WeeklyReview::factory()->for($user)->create([
            'tasks_completed' => 10,
            'tasks_missed' => 2,
        ]);

        $response = $this->actingAs($user)->get(route('weekly-reviews.show', $review));

        $response->assertOk();
        $response->assertSeeText('10');
    }

    // ---------------------------------------------------------------
    // Edit & Update
    // ---------------------------------------------------------------

    public function test_user_can_view_weekly_review_edit_form(): void
    {
        $user = User::factory()->create();
        $review = WeeklyReview::factory()->for($user)->create();

        $response = $this->actingAs($user)->get(route('weekly-reviews.edit', $review));

        $response->assertOk();
    }

    public function test_user_can_update_weekly_review(): void
    {
        $user = User::factory()->create();
        $review = WeeklyReview::factory()->for($user)->create();

        $response = $this->actingAs($user)->put(route('weekly-reviews.update', $review), [
            'week_start' => '2025-01-06',
            'week_end' => '2025-01-12',
            'tasks_completed' => 20,
            'tasks_created' => 25,
            'tasks_missed' => 5,
            'summary' => 'Updated summary.',
        ]);

        $response->assertRedirect(route('weekly-reviews.show', $review));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('weekly_reviews', [
            'id' => $review->id,
            'tasks_completed' => 20,
            'summary' => 'Updated summary.',
        ]);
    }

    public function test_update_requires_week_end_after_or_equal_to_week_start(): void
    {
        $user = User::factory()->create();
        $review = WeeklyReview::factory()->for($user)->create();

        $response = $this->actingAs($user)->put(route('weekly-reviews.update', $review), [
            'week_start' => '2025-01-12',
            'week_end' => '2025-01-06',
            'tasks_completed' => 5,
            'tasks_created' => 8,
            'tasks_missed' => 1,
        ]);

        $response->assertSessionHasErrors('week_end');
    }

    // ---------------------------------------------------------------
    // Destroy
    // ---------------------------------------------------------------

    public function test_user_can_delete_weekly_review(): void
    {
        $user = User::factory()->create();
        $review = WeeklyReview::factory()->for($user)->create();

        $response = $this->actingAs($user)->delete(route('weekly-reviews.destroy', $review));

        $response->assertRedirect(route('weekly-reviews.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('weekly_reviews', ['id' => $review->id]);
    }
}
