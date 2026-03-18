<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    // ---------------------------------------------------------------
    // Authentication Guards
    // ---------------------------------------------------------------

    public function test_guest_cannot_access_task_index(): void
    {
        $response = $this->get(route('tasks.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_access_task_create(): void
    {
        $response = $this->get(route('tasks.create'));

        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_store_task(): void
    {
        $response = $this->post(route('tasks.store'), [
            'title' => 'Test Task',
            'priority' => 'low',
            'status' => 'pending',
            'due_date' => now()->addDay()->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_view_task(): void
    {
        $task = Task::factory()->create();

        $response = $this->get(route('tasks.show', $task));

        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_edit_task(): void
    {
        $task = Task::factory()->create();

        $response = $this->get(route('tasks.edit', $task));

        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_update_task(): void
    {
        $task = Task::factory()->create();

        $response = $this->put(route('tasks.update', $task), [
            'title' => 'Updated',
            'priority' => 'low',
            'status' => 'pending',
            'due_date' => now()->addDay()->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_delete_task(): void
    {
        $task = Task::factory()->create();

        $response = $this->delete(route('tasks.destroy', $task));

        $response->assertRedirect(route('login'));
    }

    // ---------------------------------------------------------------
    // Authorization — Users cannot access other users' tasks
    // ---------------------------------------------------------------

    public function test_user_cannot_view_another_users_task(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $task = Task::factory()->for($otherUser)->create();

        $response = $this->actingAs($user)->get(route('tasks.show', $task));

        $response->assertForbidden();
    }

    public function test_user_cannot_edit_another_users_task(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $task = Task::factory()->for($otherUser)->create();

        $response = $this->actingAs($user)->get(route('tasks.edit', $task));

        $response->assertForbidden();
    }

    public function test_user_cannot_update_another_users_task(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $task = Task::factory()->for($otherUser)->create();

        $response = $this->actingAs($user)->put(route('tasks.update', $task), [
            'title' => 'Hacked',
            'priority' => 'low',
            'status' => 'pending',
            'due_date' => now()->addDay()->format('Y-m-d'),
        ]);

        $response->assertForbidden();
    }

    public function test_user_cannot_delete_another_users_task(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $task = Task::factory()->for($otherUser)->create();

        $response = $this->actingAs($user)->delete(route('tasks.destroy', $task));

        $response->assertForbidden();
    }

    public function test_index_only_shows_own_tasks(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $ownTask = Task::factory()->for($user)->create(['title' => 'My Task']);
        $otherTask = Task::factory()->for($otherUser)->create(['title' => 'Other Task']);

        $response = $this->actingAs($user)->get(route('tasks.index'));

        $response->assertOk();
        $response->assertSeeText('My Task');
        $response->assertDontSeeText('Other Task');
    }

    // ---------------------------------------------------------------
    // Index
    // ---------------------------------------------------------------

    public function test_authenticated_user_can_view_task_index(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('tasks.index'));

        $response->assertOk();
        $response->assertSeeText('Tasks');
    }

    public function test_index_shows_empty_state_when_no_tasks(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('tasks.index'));

        $response->assertOk();
        $response->assertSeeText('No tasks yet');
    }

    public function test_index_displays_tasks_with_correct_data(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create([
            'title' => 'Complete the report',
            'priority' => 'high',
            'status' => 'in_progress',
            'due_date' => now()->addDays(5)->format('Y-m-d'),
        ]);

        $response = $this->actingAs($user)->get(route('tasks.index'));

        $response->assertOk();
        $response->assertSeeText('Complete the report');
        $response->assertSeeText('High');
        $response->assertSeeText('In Progress');
    }

    public function test_index_paginates_tasks(): void
    {
        $user = User::factory()->create();
        Task::factory()->count(20)->for($user)->create();

        $response = $this->actingAs($user)->get(route('tasks.index'));

        $response->assertOk();
        // 15 per page, so page 2 should exist
        $response->assertSeeText('Showing');
    }

    public function test_index_shows_recurring_daily_badge(): void
    {
        $user = User::factory()->create();
        Task::factory()->recurringDaily()->for($user)->create();

        $response = $this->actingAs($user)->get(route('tasks.index'));

        $response->assertOk();
        $response->assertSeeText('Daily');
    }

    // ---------------------------------------------------------------
    // Create & Store
    // ---------------------------------------------------------------

    public function test_user_can_view_create_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('tasks.create'));

        $response->assertOk();
        $response->assertSeeText('Create Task');
    }

    public function test_user_can_create_task_with_valid_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('tasks.store'), [
            'title' => 'New Task',
            'description' => 'Task description here.',
            'status' => 'pending',
            'priority' => 'medium',
            'due_date' => now()->addDays(7)->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('tasks.index'));
        $response->assertSessionHas('success', 'Task created successfully.');

        $this->assertDatabaseHas('tasks', [
            'title' => 'New Task',
            'description' => 'Task description here.',
            'status' => 'pending',
            'priority' => 'medium',
            'user_id' => $user->id,
        ]);
    }

    public function test_task_is_assigned_to_authenticated_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('tasks.store'), [
            'title' => 'My task',
            'status' => 'pending',
            'priority' => 'low',
            'due_date' => now()->addDay()->format('Y-m-d'),
        ]);

        $task = Task::first();
        $this->assertNotNull($task);
        $this->assertEquals($user->id, $task->user_id);
    }

    public function test_store_requires_title(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('tasks.store'), [
            'status' => 'pending',
            'priority' => 'low',
            'due_date' => now()->addDay()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('title');
    }

    public function test_store_requires_priority(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('tasks.store'), [
            'title' => 'Test',
            'status' => 'pending',
            'due_date' => now()->addDay()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('priority');
    }

    public function test_store_requires_status(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('tasks.store'), [
            'title' => 'Test',
            'priority' => 'low',
            'due_date' => now()->addDay()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('status');
    }

    public function test_store_rejects_invalid_status(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('tasks.store'), [
            'title' => 'Test',
            'status' => 'invalid_status',
            'priority' => 'low',
            'due_date' => now()->addDay()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('status');
    }

    public function test_store_rejects_invalid_priority(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('tasks.store'), [
            'title' => 'Test',
            'status' => 'pending',
            'priority' => 'super_critical',
            'due_date' => now()->addDay()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('priority');
    }

    public function test_store_title_max_length(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('tasks.store'), [
            'title' => str_repeat('a', 256),
            'status' => 'pending',
            'priority' => 'low',
            'due_date' => now()->addDay()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('title');
    }

    public function test_store_description_max_length(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('tasks.store'), [
            'title' => 'Test',
            'description' => str_repeat('a', 5001),
            'status' => 'pending',
            'priority' => 'low',
            'due_date' => now()->addDay()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('description');
    }

    public function test_store_due_date_must_be_today_or_future(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('tasks.store'), [
            'title' => 'Test',
            'status' => 'pending',
            'priority' => 'low',
            'due_date' => now()->subDay()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('due_date');
    }

    public function test_store_due_date_required_for_non_recurring(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('tasks.store'), [
            'title' => 'Test',
            'status' => 'pending',
            'priority' => 'low',
        ]);

        $response->assertSessionHasErrors('due_date');
    }

    public function test_store_recurring_task_clears_due_date_and_requires_time(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('tasks.store'), [
            'title' => 'Daily Standup',
            'priority' => 'medium',
            'is_recurring_daily' => '1',
            'recurring_times' => ['09:00'],
        ]);

        $response->assertRedirect(route('tasks.index'));
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('tasks', [
            'title' => 'Daily Standup',
            'is_recurring_daily' => true,
            'due_date' => null,
            'status' => 'pending',
            'user_id' => $user->id,
        ]);

        $task = Task::where('title', 'Daily Standup')->first();
        $this->assertEquals(['09:00'], $task->recurring_times);
    }

    public function test_store_recurring_task_without_time_fails(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('tasks.store'), [
            'title' => 'Daily Standup',
            'priority' => 'medium',
            'is_recurring_daily' => '1',
        ]);

        $response->assertSessionHasErrors('recurring_times');
    }

    public function test_store_recurring_task_with_invalid_time_format_fails(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('tasks.store'), [
            'title' => 'Daily Standup',
            'priority' => 'medium',
            'is_recurring_daily' => '1',
            'recurring_times' => ['9am'],
        ]);

        $response->assertSessionHasErrors('recurring_times.0');
    }

    public function test_store_recurring_task_with_multiple_times(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('tasks.store'), [
            'title' => 'Multiple Times Task',
            'priority' => 'medium',
            'is_recurring_daily' => '1',
            'recurring_times' => ['09:00', '14:00', '18:30'],
        ]);

        $response->assertRedirect(route('tasks.index'));
        $response->assertSessionHasNoErrors();

        $task = Task::where('title', 'Multiple Times Task')->first();
        $this->assertNotNull($task);
        $this->assertEquals(['09:00', '14:00', '18:30'], $task->recurring_times);
    }

    public function test_store_recurring_task_rejects_duplicate_times(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('tasks.store'), [
            'title' => 'Duplicate Times',
            'priority' => 'medium',
            'is_recurring_daily' => '1',
            'recurring_times' => ['09:00', '09:00'],
        ]);

        $response->assertSessionHasErrors('recurring_times.1');
    }

    public function test_store_recurring_task_rejects_empty_times_array(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('tasks.store'), [
            'title' => 'Empty Times',
            'priority' => 'medium',
            'is_recurring_daily' => '1',
            'recurring_times' => [],
        ]);

        $response->assertSessionHasErrors('recurring_times');
    }

    public function test_store_allows_null_description(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('tasks.store'), [
            'title' => 'No description task',
            'status' => 'pending',
            'priority' => 'low',
            'due_date' => now()->addDay()->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('tasks.index'));
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('tasks', [
            'title' => 'No description task',
            'description' => null,
        ]);
    }

    public function test_store_accepts_all_valid_statuses(): void
    {
        $user = User::factory()->create();

        foreach (Task::STATUSES as $status) {
            $response = $this->actingAs($user)->post(route('tasks.store'), [
                'title' => "Task with status {$status}",
                'status' => $status,
                'priority' => 'low',
                'due_date' => now()->addDay()->format('Y-m-d'),
            ]);

            $response->assertSessionHasNoErrors();
        }

        $this->assertDatabaseCount('tasks', count(Task::STATUSES));
    }

    public function test_store_accepts_all_valid_priorities(): void
    {
        $user = User::factory()->create();

        foreach (Task::PRIORITIES as $priority) {
            $response = $this->actingAs($user)->post(route('tasks.store'), [
                'title' => "Task with priority {$priority}",
                'status' => 'pending',
                'priority' => $priority,
                'due_date' => now()->addDay()->format('Y-m-d'),
            ]);

            $response->assertSessionHasNoErrors();
        }

        $this->assertDatabaseCount('tasks', count(Task::PRIORITIES));
    }

    // ---------------------------------------------------------------
    // Show
    // ---------------------------------------------------------------

    public function test_user_can_view_own_task(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create([
            'title' => 'My Specific Task',
            'description' => 'Detailed description.',
            'priority' => 'urgent',
        ]);

        $response = $this->actingAs($user)->get(route('tasks.show', $task));

        $response->assertOk();
        $response->assertSeeText('My Specific Task');
        $response->assertSeeText('Detailed description.');
        $response->assertSeeText('Urgent');
    }

    public function test_show_displays_recurring_task_info(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->recurringDaily()->for($user)->create([
            'recurring_times' => ['14:30'],
        ]);

        $response = $this->actingAs($user)->get(route('tasks.show', $task));

        $response->assertOk();
        $response->assertSeeText('Daily Recurring');
        $response->assertSeeText('2:30 PM');
    }

    public function test_show_displays_multiple_recurring_times(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->recurringDaily()->for($user)->create([
            'recurring_times' => ['09:00', '14:30', '18:00'],
        ]);

        $response = $this->actingAs($user)->get(route('tasks.show', $task));

        $response->assertOk();
        $response->assertSeeText('9:00 AM');
        $response->assertSeeText('2:30 PM');
        $response->assertSeeText('6:00 PM');
    }

    public function test_show_displays_overdue_indicator(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->overdue()->for($user)->create();

        $response = $this->actingAs($user)->get(route('tasks.show', $task));

        $response->assertOk();
        $response->assertSeeText('Overdue');
    }

    public function test_show_displays_due_today_indicator(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create([
            'due_date' => now()->format('Y-m-d'),
        ]);

        $response = $this->actingAs($user)->get(route('tasks.show', $task));

        $response->assertOk();
        $response->assertSeeText('Due today');
    }

    // ---------------------------------------------------------------
    // Edit & Update
    // ---------------------------------------------------------------

    public function test_user_can_view_edit_form_for_own_task(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create(['title' => 'Editable Task']);

        $response = $this->actingAs($user)->get(route('tasks.edit', $task));

        $response->assertOk();
        $response->assertSeeText('Edit Task');
        $response->assertSee('Editable Task');
    }

    public function test_user_can_update_task_with_valid_data(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create();

        $response = $this->actingAs($user)->put(route('tasks.update', $task), [
            'title' => 'Updated Title',
            'description' => 'Updated description.',
            'status' => 'completed',
            'priority' => 'urgent',
            'due_date' => now()->addDays(10)->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('tasks.show', $task));
        $response->assertSessionHas('success', 'Task updated successfully.');

        $task->refresh();
        $this->assertEquals('Updated Title', $task->title);
        $this->assertEquals('Updated description.', $task->description);
        $this->assertEquals('completed', $task->status);
        $this->assertEquals('urgent', $task->priority);
    }

    public function test_update_validates_required_fields(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create();

        $response = $this->actingAs($user)->put(route('tasks.update', $task), []);

        $response->assertSessionHasErrors(['title', 'status', 'priority']);
    }

    public function test_update_rejects_invalid_status(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create();

        $response = $this->actingAs($user)->put(route('tasks.update', $task), [
            'title' => 'Test',
            'status' => 'bogus',
            'priority' => 'low',
            'due_date' => now()->addDay()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('status');
    }

    public function test_update_rejects_invalid_priority(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create();

        $response = $this->actingAs($user)->put(route('tasks.update', $task), [
            'title' => 'Test',
            'status' => 'pending',
            'priority' => 'extreme',
            'due_date' => now()->addDay()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('priority');
    }

    public function test_update_can_switch_to_recurring_daily(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create([
            'is_recurring_daily' => false,
            'due_date' => now()->addDays(5),
        ]);

        $response = $this->actingAs($user)->put(route('tasks.update', $task), [
            'title' => $task->title,
            'priority' => $task->priority,
            'is_recurring_daily' => '1',
            'recurring_times' => ['08:00'],
        ]);

        $response->assertRedirect(route('tasks.show', $task));
        $response->assertSessionHasNoErrors();

        $task->refresh();
        $this->assertTrue($task->is_recurring_daily);
        $this->assertEquals(['08:00'], $task->recurring_times);
        $this->assertNull($task->due_date);
        $this->assertEquals('pending', $task->status);
    }

    public function test_update_can_switch_from_recurring_to_dated(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->recurringDaily()->for($user)->create();

        $dueDate = now()->addDays(3)->format('Y-m-d');

        $response = $this->actingAs($user)->put(route('tasks.update', $task), [
            'title' => $task->title,
            'status' => 'in_progress',
            'priority' => 'high',
            'due_date' => $dueDate,
            'is_recurring_daily' => '0',
        ]);

        $response->assertRedirect(route('tasks.show', $task));
        $response->assertSessionHasNoErrors();

        $task->refresh();
        $this->assertFalse($task->is_recurring_daily);
        $this->assertEquals('in_progress', $task->status);
        $this->assertEquals($dueDate, $task->due_date->format('Y-m-d'));
    }

    public function test_update_recurring_task_without_time_fails(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create();

        $response = $this->actingAs($user)->put(route('tasks.update', $task), [
            'title' => 'Test',
            'priority' => 'low',
            'is_recurring_daily' => '1',
        ]);

        $response->assertSessionHasErrors('recurring_times');
    }

    // ---------------------------------------------------------------
    // Delete
    // ---------------------------------------------------------------

    public function test_user_can_delete_own_task(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create();

        $response = $this->actingAs($user)->delete(route('tasks.destroy', $task));

        $response->assertRedirect(route('tasks.index'));
        $response->assertSessionHas('success', 'Task deleted successfully.');

        $this->assertDatabaseMissing('tasks', [
            'id' => $task->id,
        ]);
    }

    public function test_deleted_task_no_longer_appears_in_index(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create(['title' => 'Doomed Task']);

        $this->actingAs($user)->delete(route('tasks.destroy', $task));

        $response = $this->actingAs($user)->get(route('tasks.index'));

        $response->assertDontSeeText('Doomed Task');
    }

    // ---------------------------------------------------------------
    // Model Helpers
    // ---------------------------------------------------------------

    public function test_task_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create();

        $this->assertInstanceOf(User::class, $task->user);
        $this->assertEquals($user->id, $task->user->id);
    }

    public function test_user_has_many_tasks(): void
    {
        $user = User::factory()->create();
        Task::factory()->count(3)->for($user)->create();

        $this->assertCount(3, $user->tasks);
    }

    public function test_task_priority_badge_classes_returns_string(): void
    {
        $task = Task::factory()->create(['priority' => 'urgent']);

        $this->assertIsString($task->priorityBadgeClasses());
        $this->assertNotEmpty($task->priorityBadgeClasses());
    }

    public function test_task_status_badge_classes_returns_string(): void
    {
        $task = Task::factory()->create(['status' => 'completed']);

        $this->assertIsString($task->statusBadgeClasses());
        $this->assertNotEmpty($task->statusBadgeClasses());
    }

    public function test_task_status_label_returns_human_readable(): void
    {
        $task = Task::factory()->create(['status' => 'in_progress']);

        $this->assertEquals('In Progress', $task->statusLabel());
    }

    public function test_task_priority_label_returns_human_readable(): void
    {
        $task = Task::factory()->create(['priority' => 'high']);

        $this->assertEquals('High', $task->priorityLabel());
    }

    public function test_task_casts_due_date_to_carbon(): void
    {
        $task = Task::factory()->create([
            'due_date' => '2026-04-15',
        ]);

        $this->assertInstanceOf(\Carbon\CarbonImmutable::class, $task->due_date);
    }

    public function test_task_casts_is_recurring_daily_to_boolean(): void
    {
        $task = Task::factory()->recurringDaily()->create();

        $this->assertIsBool($task->is_recurring_daily);
        $this->assertTrue($task->is_recurring_daily);
    }

    // ---------------------------------------------------------------
    // Cascade Delete
    // ---------------------------------------------------------------

    public function test_deleting_user_deletes_their_tasks(): void
    {
        $user = User::factory()->create();
        Task::factory()->count(3)->for($user)->create();

        $this->assertDatabaseCount('tasks', 3);

        $user->delete();

        $this->assertDatabaseCount('tasks', 0);
    }

    // ---------------------------------------------------------------
    // Mass Assignment Protection
    // ---------------------------------------------------------------

    public function test_user_cannot_set_user_id_via_store(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $this->actingAs($user)->post(route('tasks.store'), [
            'title' => 'Sneaky Task',
            'status' => 'pending',
            'priority' => 'low',
            'due_date' => now()->addDay()->format('Y-m-d'),
            'user_id' => $otherUser->id,
        ]);

        $task = Task::where('title', 'Sneaky Task')->first();

        $this->assertNotNull($task);
        $this->assertEquals($user->id, $task->user_id);
    }

    // ---------------------------------------------------------------
    // Flash Messages
    // ---------------------------------------------------------------

    public function test_success_message_displayed_after_create(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('tasks.store'), [
            'title' => 'Flash Test',
            'status' => 'pending',
            'priority' => 'low',
            'due_date' => now()->addDay()->format('Y-m-d'),
        ]);

        $response->assertSessionHas('success');

        $indexResponse = $this->actingAs($user)->get(route('tasks.index'));
        $indexResponse->assertSeeText('Task created successfully.');
    }

    public function test_success_message_displayed_after_update(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create();

        $response = $this->actingAs($user)->put(route('tasks.update', $task), [
            'title' => 'Updated',
            'status' => 'pending',
            'priority' => 'low',
            'due_date' => now()->addDay()->format('Y-m-d'),
        ]);

        $response->assertSessionHas('success', 'Task updated successfully.');
    }

    public function test_success_message_displayed_after_delete(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create();

        $response = $this->actingAs($user)->delete(route('tasks.destroy', $task));

        $response->assertSessionHas('success', 'Task deleted successfully.');
    }

    // ---------------------------------------------------------------
    // Edge Cases
    // ---------------------------------------------------------------

    public function test_store_with_today_as_due_date_succeeds(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('tasks.store'), [
            'title' => 'Due today task',
            'status' => 'pending',
            'priority' => 'low',
            'due_date' => now()->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('tasks.index'));
        $response->assertSessionHasNoErrors();
    }

    public function test_store_title_at_max_length_succeeds(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('tasks.store'), [
            'title' => str_repeat('a', 255),
            'status' => 'pending',
            'priority' => 'low',
            'due_date' => now()->addDay()->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('tasks.index'));
        $response->assertSessionHasNoErrors();
    }

    public function test_store_description_at_max_length_succeeds(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('tasks.store'), [
            'title' => 'Test',
            'description' => str_repeat('a', 5000),
            'status' => 'pending',
            'priority' => 'low',
            'due_date' => now()->addDay()->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('tasks.index'));
        $response->assertSessionHasNoErrors();
    }

    public function test_nonexistent_task_redirects_with_error(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('tasks.show', 99999));

        $response->assertRedirect(route('tasks.index'));
        $response->assertSessionHas('error');
    }

    public function test_update_nonexistent_task_redirects_with_error(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put(route('tasks.update', 99999), [
            'title' => 'Test',
            'status' => 'pending',
            'priority' => 'low',
            'due_date' => now()->addDay()->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('tasks.index'));
        $response->assertSessionHas('error');
    }

    public function test_delete_nonexistent_task_redirects_with_error(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->delete(route('tasks.destroy', 99999));

        $response->assertRedirect(route('tasks.index'));
        $response->assertSessionHas('error');
    }

    // ---------------------------------------------------------------
    // Policy Method Coverage
    // ---------------------------------------------------------------

    public function test_all_priority_badge_classes_are_defined(): void
    {
        foreach (Task::PRIORITIES as $priority) {
            $task = Task::factory()->create(['priority' => $priority]);
            $this->assertIsString($task->priorityBadgeClasses());
            $this->assertNotEmpty($task->priorityBadgeClasses());
        }
    }

    public function test_all_status_badge_classes_are_defined(): void
    {
        foreach (Task::STATUSES as $status) {
            $task = Task::factory()->create(['status' => $status]);
            $this->assertIsString($task->statusBadgeClasses());
            $this->assertNotEmpty($task->statusBadgeClasses());
        }
    }

    public function test_all_status_labels_are_defined(): void
    {
        $expectedLabels = [
            'pending' => 'Pending',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
        ];

        foreach ($expectedLabels as $status => $label) {
            $task = Task::factory()->create(['status' => $status]);
            $this->assertEquals($label, $task->statusLabel());
        }
    }
}
