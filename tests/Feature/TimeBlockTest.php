<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\TimeBlock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimeBlockTest extends TestCase
{
    use RefreshDatabase;

    // ---------------------------------------------------------------
    // Authentication Guards
    // ---------------------------------------------------------------

    public function test_guest_cannot_access_time_block_index(): void
    {
        $response = $this->get(route('time-blocks.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_access_time_block_create(): void
    {
        $response = $this->get(route('time-blocks.create'));

        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_store_time_block(): void
    {
        $response = $this->post(route('time-blocks.store'), [
            'title' => 'Focus session',
            'date' => today()->format('Y-m-d'),
            'start_time' => '09:00',
            'end_time' => '10:00',
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_edit_time_block(): void
    {
        $block = TimeBlock::factory()->create();

        $response = $this->get(route('time-blocks.edit', $block));

        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_update_time_block(): void
    {
        $block = TimeBlock::factory()->create();

        $response = $this->put(route('time-blocks.update', $block), [
            'title' => 'Updated',
            'date' => today()->format('Y-m-d'),
            'start_time' => '09:00',
            'end_time' => '10:00',
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_delete_time_block(): void
    {
        $block = TimeBlock::factory()->create();

        $response = $this->delete(route('time-blocks.destroy', $block));

        $response->assertRedirect(route('login'));
    }

    // ---------------------------------------------------------------
    // Authorization — Users cannot access other users' time blocks
    // ---------------------------------------------------------------

    public function test_user_cannot_edit_another_users_time_block(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $block = TimeBlock::factory()->for($otherUser)->create();

        $response = $this->actingAs($user)->get(route('time-blocks.edit', $block));

        $response->assertForbidden();
    }

    public function test_user_cannot_update_another_users_time_block(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $block = TimeBlock::factory()->for($otherUser)->create();

        $response = $this->actingAs($user)->put(route('time-blocks.update', $block), [
            'title' => 'Hacked',
            'date' => today()->format('Y-m-d'),
            'start_time' => '09:00',
            'end_time' => '10:00',
        ]);

        $response->assertForbidden();
    }

    public function test_user_cannot_delete_another_users_time_block(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $block = TimeBlock::factory()->for($otherUser)->create();

        $response = $this->actingAs($user)->delete(route('time-blocks.destroy', $block));

        $response->assertForbidden();
    }

    public function test_store_rejects_task_belonging_to_another_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $otherTask = Task::factory()->for($otherUser)->create(['status' => 'pending', 'due_date' => now()->addDay()->format('Y-m-d')]);

        $response = $this->actingAs($user)->post(route('time-blocks.store'), [
            'title' => 'Block',
            'date' => today()->format('Y-m-d'),
            'start_time' => '09:00',
            'end_time' => '10:00',
            'task_id' => $otherTask->id,
        ]);

        $response->assertSessionHasErrors('task_id');
    }

    public function test_update_rejects_task_belonging_to_another_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $block = TimeBlock::factory()->for($user)->create();
        $otherTask = Task::factory()->for($otherUser)->create(['status' => 'pending', 'due_date' => now()->addDay()->format('Y-m-d')]);

        $response = $this->actingAs($user)->put(route('time-blocks.update', $block), [
            'title' => 'Block',
            'date' => today()->format('Y-m-d'),
            'start_time' => '09:00',
            'end_time' => '10:00',
            'task_id' => $otherTask->id,
        ]);

        $response->assertSessionHasErrors('task_id');
    }

    // ---------------------------------------------------------------
    // Index
    // ---------------------------------------------------------------

    public function test_authenticated_user_can_view_time_block_index(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('time-blocks.index'));

        $response->assertOk();
        $response->assertSeeText('Time Blocks');
    }

    public function test_index_defaults_to_today(): void
    {
        $user = User::factory()->create();
        TimeBlock::factory()->for($user)->create([
            'title' => 'Today Block',
            'date' => today()->format('Y-m-d'),
        ]);

        $response = $this->actingAs($user)->get(route('time-blocks.index'));

        $response->assertOk();
        $response->assertSeeText('Today Block');
    }

    public function test_index_can_filter_by_date(): void
    {
        $user = User::factory()->create();
        $tomorrow = now()->addDay()->format('Y-m-d');
        TimeBlock::factory()->for($user)->create([
            'title' => 'Tomorrow Block',
            'date' => $tomorrow,
        ]);

        $response = $this->actingAs($user)->get(route('time-blocks.index', ['date' => $tomorrow]));

        $response->assertOk();
        $response->assertSee('data-test="day-blocks"', false);
        $response->assertSeeText('Tomorrow Block');
    }

    public function test_index_only_shows_own_time_blocks(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        TimeBlock::factory()->for($user)->create([
            'title' => 'My Block',
            'date' => today()->format('Y-m-d'),
        ]);
        TimeBlock::factory()->for($otherUser)->create([
            'title' => 'Other Block',
            'date' => today()->format('Y-m-d'),
        ]);

        $response = $this->actingAs($user)->get(route('time-blocks.index'));

        $response->assertOk();
        $response->assertSeeText('My Block');
        $response->assertDontSeeText('Other Block');
    }

    public function test_index_falls_back_to_today_on_invalid_date(): void
    {
        $user = User::factory()->create();
        TimeBlock::factory()->for($user)->create([
            'title' => 'Today Block',
            'date' => today()->format('Y-m-d'),
        ]);

        $response = $this->actingAs($user)->get(route('time-blocks.index', ['date' => 'not-a-date']));

        $response->assertOk();
        $response->assertSeeText('Today Block');
    }

    public function test_index_shows_all_time_blocks_section(): void
    {
        $user = User::factory()->create();
        TimeBlock::factory()->for($user)->create([
            'title' => 'Past Block',
            'date' => now()->subDays(5)->format('Y-m-d'),
        ]);

        $response = $this->actingAs($user)->get(route('time-blocks.index'));

        $response->assertOk();
        $response->assertSeeText('All Time Blocks');
        $response->assertSeeText('Past Block');
    }

    public function test_index_all_blocks_excludes_other_users_blocks(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        TimeBlock::factory()->for($user)->create(['title' => 'My Past Block', 'date' => now()->subDay()->format('Y-m-d')]);
        TimeBlock::factory()->for($otherUser)->create(['title' => 'Other Past Block', 'date' => now()->subDay()->format('Y-m-d')]);

        $response = $this->actingAs($user)->get(route('time-blocks.index'));

        $response->assertOk();
        $response->assertSeeText('My Past Block');
        $response->assertDontSeeText('Other Past Block');
    }

    // ---------------------------------------------------------------
    // Create
    // ---------------------------------------------------------------

    public function test_user_can_view_time_block_create_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('time-blocks.create'));

        $response->assertOk();
    }

    public function test_create_form_lists_non_completed_tasks(): void
    {
        $user = User::factory()->create();
        Task::factory()->for($user)->create(['title' => 'Pending Task', 'status' => 'pending', 'due_date' => now()->addDay()->format('Y-m-d')]);
        Task::factory()->completed()->for($user)->create(['title' => 'Done Task']);

        $response = $this->actingAs($user)->get(route('time-blocks.create'));

        $response->assertOk();
        $response->assertSeeText('Pending Task');
        $response->assertDontSeeText('Done Task');
    }

    public function test_create_form_only_lists_own_tasks(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        Task::factory()->for($user)->create(['title' => 'My Task', 'status' => 'pending', 'due_date' => now()->addDay()->format('Y-m-d')]);
        Task::factory()->for($otherUser)->create(['title' => 'Other Task', 'status' => 'pending', 'due_date' => now()->addDay()->format('Y-m-d')]);

        $response = $this->actingAs($user)->get(route('time-blocks.create'));

        $response->assertOk();
        $response->assertSeeText('My Task');
        $response->assertDontSeeText('Other Task');
    }

    // ---------------------------------------------------------------
    // Store
    // ---------------------------------------------------------------

    public function test_user_can_create_time_block(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('time-blocks.store'), [
            'title' => 'Deep work',
            'date' => today()->format('Y-m-d'),
            'start_time' => '09:00',
            'end_time' => '10:30',
        ]);

        $response->assertRedirect(route('time-blocks.index'));
        $response->assertSessionHas('success', 'Time block created successfully.');

        $this->assertDatabaseHas('time_blocks', [
            'user_id' => $user->id,
            'title' => 'Deep work',
            'date' => today()->format('Y-m-d'),
        ]);
    }

    public function test_time_block_assigned_to_authenticated_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('time-blocks.store'), [
            'title' => 'My block',
            'date' => today()->format('Y-m-d'),
            'start_time' => '08:00',
            'end_time' => '09:00',
        ]);

        $this->assertDatabaseHas('time_blocks', [
            'user_id' => $user->id,
        ]);
    }

    public function test_store_requires_title(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('time-blocks.store'), [
            'date' => today()->format('Y-m-d'),
            'start_time' => '09:00',
            'end_time' => '10:00',
        ]);

        $response->assertSessionHasErrors('title');
    }

    public function test_store_rejects_title_exceeding_255_chars(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('time-blocks.store'), [
            'title' => str_repeat('a', 256),
            'date' => today()->format('Y-m-d'),
            'start_time' => '09:00',
            'end_time' => '10:00',
        ]);

        $response->assertSessionHasErrors('title');
    }

    public function test_store_requires_date(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('time-blocks.store'), [
            'title' => 'Block',
            'start_time' => '09:00',
            'end_time' => '10:00',
        ]);

        $response->assertSessionHasErrors('date');
    }

    public function test_store_rejects_past_date(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('time-blocks.store'), [
            'title' => 'Block',
            'date' => now()->subDay()->format('Y-m-d'),
            'start_time' => '09:00',
            'end_time' => '10:00',
        ]);

        $response->assertSessionHasErrors('date');
    }

    public function test_store_requires_start_time(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('time-blocks.store'), [
            'title' => 'Block',
            'date' => today()->format('Y-m-d'),
            'end_time' => '10:00',
        ]);

        $response->assertSessionHasErrors('start_time');
    }

    public function test_store_rejects_invalid_start_time_format(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('time-blocks.store'), [
            'title' => 'Block',
            'date' => today()->format('Y-m-d'),
            'start_time' => '9am',
            'end_time' => '10:00',
        ]);

        $response->assertSessionHasErrors('start_time');
    }

    public function test_store_requires_end_time(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('time-blocks.store'), [
            'title' => 'Block',
            'date' => today()->format('Y-m-d'),
            'start_time' => '09:00',
        ]);

        $response->assertSessionHasErrors('end_time');
    }

    public function test_store_rejects_end_time_before_start_time(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('time-blocks.store'), [
            'title' => 'Block',
            'date' => today()->format('Y-m-d'),
            'start_time' => '10:00',
            'end_time' => '09:00',
        ]);

        $response->assertSessionHasErrors('end_time');
    }

    public function test_store_rejects_end_time_equal_to_start_time(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('time-blocks.store'), [
            'title' => 'Block',
            'date' => today()->format('Y-m-d'),
            'start_time' => '09:00',
            'end_time' => '09:00',
        ]);

        $response->assertSessionHasErrors('end_time');
    }

    public function test_store_optional_task_id(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create(['status' => 'pending', 'due_date' => now()->addDay()->format('Y-m-d')]);

        $response = $this->actingAs($user)->post(route('time-blocks.store'), [
            'title' => 'Block',
            'date' => today()->format('Y-m-d'),
            'start_time' => '09:00',
            'end_time' => '10:00',
            'task_id' => $task->id,
        ]);

        $response->assertRedirect(route('time-blocks.index'));
        $this->assertDatabaseHas('time_blocks', [
            'task_id' => $task->id,
        ]);
    }

    public function test_store_rejects_non_existent_task_id(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('time-blocks.store'), [
            'title' => 'Block',
            'date' => today()->format('Y-m-d'),
            'start_time' => '09:00',
            'end_time' => '10:00',
            'task_id' => 9999,
        ]);

        $response->assertSessionHasErrors('task_id');
    }

    public function test_store_optional_estimated_minutes(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('time-blocks.store'), [
            'title' => 'Block',
            'date' => today()->format('Y-m-d'),
            'start_time' => '09:00',
            'end_time' => '10:00',
            'estimated_minutes' => 60,
        ]);

        $response->assertRedirect(route('time-blocks.index'));
        $this->assertDatabaseHas('time_blocks', [
            'estimated_minutes' => 60,
        ]);
    }

    public function test_store_rejects_estimated_minutes_above_480(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('time-blocks.store'), [
            'title' => 'Block',
            'date' => today()->format('Y-m-d'),
            'start_time' => '09:00',
            'end_time' => '10:00',
            'estimated_minutes' => 481,
        ]);

        $response->assertSessionHasErrors('estimated_minutes');
    }

    // ---------------------------------------------------------------
    // Edit & Update
    // ---------------------------------------------------------------

    public function test_user_can_view_time_block_edit_form(): void
    {
        $user = User::factory()->create();
        $block = TimeBlock::factory()->for($user)->create();

        $response = $this->actingAs($user)->get(route('time-blocks.edit', $block));

        $response->assertOk();
        $response->assertSeeText($block->title);
    }

    public function test_edit_form_only_lists_own_tasks(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $block = TimeBlock::factory()->for($user)->create();
        Task::factory()->for($user)->create(['title' => 'My Task', 'status' => 'pending', 'due_date' => now()->addDay()->format('Y-m-d')]);
        Task::factory()->for($otherUser)->create(['title' => 'Other Task', 'status' => 'pending', 'due_date' => now()->addDay()->format('Y-m-d')]);

        $response = $this->actingAs($user)->get(route('time-blocks.edit', $block));

        $response->assertOk();
        $response->assertSeeText('My Task');
        $response->assertDontSeeText('Other Task');
    }

    public function test_user_can_update_time_block(): void
    {
        $user = User::factory()->create();
        $block = TimeBlock::factory()->for($user)->create();

        $response = $this->actingAs($user)->put(route('time-blocks.update', $block), [
            'title' => 'Updated Block',
            'date' => today()->format('Y-m-d'),
            'start_time' => '14:00',
            'end_time' => '15:00',
        ]);

        $response->assertRedirect(route('time-blocks.index'));
        $response->assertSessionHas('success', 'Time block updated successfully.');

        $this->assertDatabaseHas('time_blocks', [
            'id' => $block->id,
            'title' => 'Updated Block',
        ]);
    }

    public function test_update_requires_title(): void
    {
        $user = User::factory()->create();
        $block = TimeBlock::factory()->for($user)->create();

        $response = $this->actingAs($user)->put(route('time-blocks.update', $block), [
            'date' => today()->format('Y-m-d'),
            'start_time' => '09:00',
            'end_time' => '10:00',
        ]);

        $response->assertSessionHasErrors('title');
    }

    public function test_update_rejects_end_time_before_start_time(): void
    {
        $user = User::factory()->create();
        $block = TimeBlock::factory()->for($user)->create();

        $response = $this->actingAs($user)->put(route('time-blocks.update', $block), [
            'title' => 'Block',
            'date' => today()->format('Y-m-d'),
            'start_time' => '10:00',
            'end_time' => '09:00',
        ]);

        $response->assertSessionHasErrors('end_time');
    }

    // ---------------------------------------------------------------
    // Destroy
    // ---------------------------------------------------------------

    public function test_user_can_delete_time_block(): void
    {
        $user = User::factory()->create();
        $block = TimeBlock::factory()->for($user)->create();

        $response = $this->actingAs($user)->delete(route('time-blocks.destroy', $block));

        $response->assertRedirect(route('time-blocks.index'));
        $response->assertSessionHas('success', 'Time block deleted successfully.');

        $this->assertDatabaseMissing('time_blocks', ['id' => $block->id]);
    }
}
