<?php

namespace Tests\Feature;

use App\Models\MoodLog;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MoodLogTest extends TestCase
{
    use RefreshDatabase;

    // ---------------------------------------------------------------
    // Authentication Guards
    // ---------------------------------------------------------------

    public function test_guest_cannot_access_mood_log_index(): void
    {
        $response = $this->get(route('mood-logs.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_store_mood_log(): void
    {
        $response = $this->post(route('mood-logs.store'), [
            'mood' => 'neutral',
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_delete_mood_log(): void
    {
        $log = MoodLog::factory()->create();

        $response = $this->delete(route('mood-logs.destroy', $log));

        $response->assertRedirect(route('login'));
    }

    // ---------------------------------------------------------------
    // Authorization — Users cannot access other users' mood logs
    // ---------------------------------------------------------------

    public function test_user_cannot_delete_another_users_mood_log(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $log = MoodLog::factory()->for($otherUser)->create();

        $response = $this->actingAs($user)->delete(route('mood-logs.destroy', $log));

        $response->assertForbidden();
    }

    // ---------------------------------------------------------------
    // Index
    // ---------------------------------------------------------------

    public function test_authenticated_user_can_view_mood_log_index(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('mood-logs.index'));

        $response->assertOk();
        $response->assertSeeText('Mood');
    }

    public function test_index_shows_own_mood_logs(): void
    {
        $user = User::factory()->create();
        MoodLog::factory()->for($user)->create([
            'mood' => 'energized',
            'note' => 'Feeling great today',
        ]);

        $response = $this->actingAs($user)->get(route('mood-logs.index'));

        $response->assertOk();
        $response->assertSeeText('Energized');
    }

    public function test_index_does_not_show_other_users_mood_logs(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        MoodLog::factory()->for($otherUser)->create(['note' => 'Other user note']);

        $response = $this->actingAs($user)->get(route('mood-logs.index'));

        $response->assertOk();
        $response->assertDontSeeText('Other user note');
    }

    public function test_index_shows_mood_distribution(): void
    {
        $user = User::factory()->create();
        MoodLog::factory()->count(3)->for($user)->create(['mood' => 'energized']);
        MoodLog::factory()->for($user)->create(['mood' => 'neutral']);

        $response = $this->actingAs($user)->get(route('mood-logs.index'));

        $response->assertOk();
        $response->assertSeeText('3');
    }

    public function test_index_paginates_mood_logs(): void
    {
        $user = User::factory()->create();
        MoodLog::factory()->count(20)->for($user)->create();

        $response = $this->actingAs($user)->get(route('mood-logs.index'));

        $response->assertOk();
    }

    // ---------------------------------------------------------------
    // Store
    // ---------------------------------------------------------------

    public function test_user_can_store_mood_log_with_valid_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('mood-logs.store'), [
            'mood' => 'energized',
            'note' => 'Great productive session',
        ]);

        $response->assertRedirect(route('mood-logs.index'));
        $response->assertSessionHas('success', 'Mood logged.');

        $this->assertDatabaseHas('mood_logs', [
            'user_id' => $user->id,
            'mood' => 'energized',
            'note' => 'Great productive session',
        ]);
    }

    public function test_mood_log_is_assigned_to_authenticated_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('mood-logs.store'), [
            'mood' => 'neutral',
        ]);

        $this->assertDatabaseHas('mood_logs', [
            'user_id' => $user->id,
        ]);
    }

    public function test_mood_log_sets_logged_at_automatically(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('mood-logs.store'), [
            'mood' => 'neutral',
        ]);

        $log = MoodLog::where('user_id', $user->id)->first();
        $this->assertNotNull($log);
        $this->assertNotNull($log->logged_at);
    }

    public function test_store_requires_mood(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('mood-logs.store'), []);

        $response->assertSessionHasErrors('mood');
    }

    public function test_store_rejects_invalid_mood_value(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('mood-logs.store'), [
            'mood' => 'angry',
        ]);

        $response->assertSessionHasErrors('mood');
    }

    public function test_store_accepts_all_valid_mood_values(): void
    {
        $user = User::factory()->create();

        foreach (['energized', 'neutral', 'drained'] as $mood) {
            $response = $this->actingAs($user)->post(route('mood-logs.store'), [
                'mood' => $mood,
            ]);

            $response->assertRedirect(route('mood-logs.index'));
            $response->assertSessionHasNoErrors();
        }
    }

    public function test_note_is_optional(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('mood-logs.store'), [
            'mood' => 'neutral',
        ]);

        $response->assertRedirect(route('mood-logs.index'));
        $response->assertSessionHasNoErrors();
    }

    public function test_store_rejects_note_exceeding_1000_chars(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('mood-logs.store'), [
            'mood' => 'neutral',
            'note' => str_repeat('a', 1001),
        ]);

        $response->assertSessionHasErrors('note');
    }

    public function test_store_accepts_optional_task_id(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create();

        $response = $this->actingAs($user)->post(route('mood-logs.store'), [
            'mood' => 'energized',
            'task_id' => $task->id,
        ]);

        $response->assertRedirect(route('mood-logs.index'));
        $this->assertDatabaseHas('mood_logs', [
            'user_id' => $user->id,
            'task_id' => $task->id,
        ]);
    }

    public function test_store_rejects_non_existent_task_id(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('mood-logs.store'), [
            'mood' => 'neutral',
            'task_id' => 9999,
        ]);

        $response->assertSessionHasErrors('task_id');
    }

    // ---------------------------------------------------------------
    // Destroy
    // ---------------------------------------------------------------

    public function test_user_can_delete_mood_log(): void
    {
        $user = User::factory()->create();
        $log = MoodLog::factory()->for($user)->create();

        $response = $this->actingAs($user)->delete(route('mood-logs.destroy', $log));

        $response->assertRedirect(route('mood-logs.index'));
        $response->assertSessionHas('success', 'Mood log deleted.');

        $this->assertDatabaseMissing('mood_logs', ['id' => $log->id]);
    }
}
