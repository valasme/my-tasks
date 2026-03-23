<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use Carbon\CarbonImmutable;
use Database\Seeders\TaskSeeder;
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

        Task::factory()->for($user)->create(['title' => 'My Task']);
        Task::factory()->for($otherUser)->create(['title' => 'Other Task']);

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
        Task::factory()->for($user)->create([
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

    public function test_index_shows_task_count(): void
    {
        $user = User::factory()->create();
        Task::factory()->count(3)->for($user)->create();

        $response = $this->actingAs($user)->get(route('tasks.index'));

        $response->assertOk();
        $response->assertSeeText('(3)');
    }

    public function test_task_seeder_assigns_tasks_to_users_workspaces(): void
    {
        $user = User::factory()->create();
        Workspace::factory()->count(4)->for($user)->create();

        $this->seed(TaskSeeder::class);

        $seededTasks = Task::query()
            ->with('workspace')
            ->where('user_id', $user->id)
            ->get();

        $this->assertCount(23, $seededTasks);
        $this->assertTrue($seededTasks->every(fn (Task $task): bool => $task->workspace_id !== null));
        $this->assertTrue($seededTasks->every(fn (Task $task): bool => $task->workspace?->user_id === $user->id));
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

    public function test_show_displays_schedule_status(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create([
            'status' => 'pending',
            'due_date' => now()->addDays(5)->format('Y-m-d'),
        ]);

        $response = $this->actingAs($user)->get(route('tasks.show', $task));

        $response->assertOk();
        $response->assertSeeText('Schedule Status');
        $response->assertSeeText('Pending');
    }

    public function test_show_displays_estimated_time(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->withEstimate(90)->for($user)->create();

        $response = $this->actingAs($user)->get(route('tasks.show', $task));

        $response->assertOk();
        $response->assertSeeText('Estimated Time');
        $response->assertSeeText('1h 30m');
    }

    public function test_show_displays_no_estimate_message(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create(['estimated_minutes' => null]);

        $response = $this->actingAs($user)->get(route('tasks.show', $task));

        $response->assertOk();
        $response->assertSeeText('No estimate set');
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

        $this->assertInstanceOf(CarbonImmutable::class, $task->due_date);
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

    // ---------------------------------------------------------------
    // Sorting
    // ---------------------------------------------------------------

    public function test_index_default_sort_is_newest_first(): void
    {
        $user = User::factory()->create();

        Task::factory()->for($user)->create(['title' => 'Older Task', 'created_at' => now()->subDay()]);
        Task::factory()->for($user)->create(['title' => 'Newer Task', 'created_at' => now()]);

        $response = $this->actingAs($user)->get(route('tasks.index'));

        $response->assertOk();
        $response->assertSeeTextInOrder(['Newer Task', 'Older Task']);
    }

    public function test_index_sort_title_asc(): void
    {
        $user = User::factory()->create();

        Task::factory()->for($user)->create(['title' => 'Zebra Task']);
        Task::factory()->for($user)->create(['title' => 'Apple Task']);

        $response = $this->actingAs($user)->get(route('tasks.index', ['sort' => 'title_asc']));

        $response->assertOk();
        $response->assertSeeTextInOrder(['Apple Task', 'Zebra Task']);
    }

    public function test_index_sort_title_desc(): void
    {
        $user = User::factory()->create();

        Task::factory()->for($user)->create(['title' => 'Apple Task']);
        Task::factory()->for($user)->create(['title' => 'Zebra Task']);

        $response = $this->actingAs($user)->get(route('tasks.index', ['sort' => 'title_desc']));

        $response->assertOk();
        $response->assertSeeTextInOrder(['Zebra Task', 'Apple Task']);
    }

    public function test_index_sort_oldest_first(): void
    {
        $user = User::factory()->create();

        Task::factory()->for($user)->create(['title' => 'Newer Task', 'created_at' => now()]);
        Task::factory()->for($user)->create(['title' => 'Older Task', 'created_at' => now()->subDay()]);

        $response = $this->actingAs($user)->get(route('tasks.index', ['sort' => 'oldest']));

        $response->assertOk();
        $response->assertSeeTextInOrder(['Older Task', 'Newer Task']);
    }

    public function test_index_sort_by_due_date_asc(): void
    {
        $user = User::factory()->create();

        Task::factory()->for($user)->create(['title' => 'Later Task', 'due_date' => now()->addDays(10)->format('Y-m-d')]);
        Task::factory()->for($user)->create(['title' => 'Sooner Task', 'due_date' => now()->addDay()->format('Y-m-d')]);

        $response = $this->actingAs($user)->get(route('tasks.index', ['sort' => 'due_date_asc']));

        $response->assertOk();
        $response->assertSeeTextInOrder(['Sooner Task', 'Later Task']);
    }

    public function test_index_invalid_sort_falls_back_to_default(): void
    {
        $user = User::factory()->create();

        Task::factory()->for($user)->create(['title' => 'Older Task', 'created_at' => now()->subDay()]);
        Task::factory()->for($user)->create(['title' => 'Newer Task', 'created_at' => now()]);

        $response = $this->actingAs($user)->get(route('tasks.index', ['sort' => 'invalid']));

        $response->assertOk();
        $response->assertSeeTextInOrder(['Newer Task', 'Older Task']);
    }

    public function test_index_sort_preserved_across_pagination(): void
    {
        $user = User::factory()->create();
        Task::factory()->count(20)->for($user)->create();

        $response = $this->actingAs($user)->get(route('tasks.index', ['sort' => 'title_asc', 'page' => 2]));

        $response->assertOk();
    }

    public function test_index_sort_dropdown_is_visible(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('tasks.index'));

        $response->assertOk();
        $response->assertSee('data-test="sort-select"', false);
    }

    // ---------------------------------------------------------------
    // Filtering
    // ---------------------------------------------------------------

    public function test_index_filter_by_status(): void
    {
        $user = User::factory()->create();

        Task::factory()->for($user)->create(['title' => 'Pending Task', 'status' => 'pending']);
        Task::factory()->for($user)->create(['title' => 'Completed Task', 'status' => 'completed']);

        $response = $this->actingAs($user)->get(route('tasks.index', ['status' => 'pending']));

        $response->assertOk();
        $response->assertSeeText('Pending Task');
        $response->assertDontSeeText('Completed Task');
    }

    public function test_index_filter_by_priority(): void
    {
        $user = User::factory()->create();

        Task::factory()->for($user)->create(['title' => 'Urgent Task', 'priority' => 'urgent']);
        Task::factory()->for($user)->create(['title' => 'Low Task', 'priority' => 'low']);

        $response = $this->actingAs($user)->get(route('tasks.index', ['priority' => 'urgent']));

        $response->assertOk();
        $response->assertSeeText('Urgent Task');
        $response->assertDontSeeText('Low Task');
    }

    public function test_index_filter_by_workspace(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->for($user)->create(['name' => 'Work']);

        Task::factory()->for($user)->create(['title' => 'Work Task', 'workspace_id' => $workspace->id]);
        Task::factory()->for($user)->create(['title' => 'Personal Task', 'workspace_id' => null]);

        $response = $this->actingAs($user)->get(route('tasks.index', ['workspace' => $workspace->id]));

        $response->assertOk();
        $response->assertSeeText('Work Task');
        $response->assertDontSeeText('Personal Task');
    }

    public function test_index_ignores_workspace_filter_from_another_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $otherUsersWorkspace = Workspace::factory()->for($otherUser)->create();

        Task::factory()->for($user)->create(['title' => 'Visible Task One']);
        Task::factory()->for($user)->create(['title' => 'Visible Task Two']);

        $response = $this->actingAs($user)->get(route('tasks.index', [
            'workspace' => $otherUsersWorkspace->id,
        ]));

        $response->assertOk();
        $response->assertSeeText('Visible Task One');
        $response->assertSeeText('Visible Task Two');
    }

    public function test_index_ignores_malformed_workspace_filter(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->for($user)->create();

        Task::factory()->for($user)->create(['title' => 'Task A', 'workspace_id' => $workspace->id]);
        Task::factory()->for($user)->create(['title' => 'Task B', 'workspace_id' => null]);

        $response = $this->actingAs($user)->get(route('tasks.index', [
            'workspace' => $workspace->id.'-oops',
        ]));

        $response->assertOk();
        $response->assertSeeText('Task A');
        $response->assertSeeText('Task B');
    }

    public function test_index_search_by_title(): void
    {
        $user = User::factory()->create();

        Task::factory()->for($user)->create(['title' => 'Buy groceries']);
        Task::factory()->for($user)->create(['title' => 'Write report']);

        $response = $this->actingAs($user)->get(route('tasks.index', ['search' => 'groceries']));

        $response->assertOk();
        $response->assertSeeText('Buy groceries');
        $response->assertDontSeeText('Write report');
    }

    public function test_index_search_by_description(): void
    {
        $user = User::factory()->create();

        Task::factory()->for($user)->create(['title' => 'Task A', 'description' => 'Contains keyword banana']);
        Task::factory()->for($user)->create(['title' => 'Task B', 'description' => 'Something else entirely']);

        $response = $this->actingAs($user)->get(route('tasks.index', ['search' => 'banana']));

        $response->assertOk();
        $response->assertSeeText('Task A');
        $response->assertDontSeeText('Task B');
    }

    public function test_index_combined_filters(): void
    {
        $user = User::factory()->create();

        Task::factory()->for($user)->create(['title' => 'Match', 'status' => 'pending', 'priority' => 'high']);
        Task::factory()->for($user)->create(['title' => 'Wrong Status', 'status' => 'completed', 'priority' => 'high']);
        Task::factory()->for($user)->create(['title' => 'Wrong Priority', 'status' => 'pending', 'priority' => 'low']);

        $response = $this->actingAs($user)->get(route('tasks.index', [
            'status' => 'pending',
            'priority' => 'high',
        ]));

        $response->assertOk();
        $response->assertSeeText('Match');
        $response->assertDontSeeText('Wrong Status');
        $response->assertDontSeeText('Wrong Priority');
    }

    public function test_index_invalid_filter_values_are_ignored(): void
    {
        $user = User::factory()->create();
        Task::factory()->count(3)->for($user)->create();

        $response = $this->actingAs($user)->get(route('tasks.index', [
            'status' => 'nonexistent',
            'priority' => 'super_critical',
        ]));

        $response->assertOk();
        $response->assertSeeText('(3)');
    }

    public function test_index_shows_no_matching_tasks_message_with_filters(): void
    {
        $user = User::factory()->create();
        Task::factory()->for($user)->create(['status' => 'pending']);

        $response = $this->actingAs($user)->get(route('tasks.index', ['status' => 'completed']));

        $response->assertOk();
        $response->assertSeeText('No matching tasks');
    }

    public function test_index_clear_filters_button_visible_when_filtered(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('tasks.index', ['status' => 'pending']));

        $response->assertOk();
        $response->assertSee('data-test="clear-filters"', false);
    }

    public function test_index_filter_ui_elements_present(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('tasks.index'));

        $response->assertOk();
        $response->assertSee('data-test="search-input"', false);
        $response->assertSee('data-test="filter-status"', false);
        $response->assertSee('data-test="filter-priority"', false);
        $response->assertSee('data-test="filter-workspace"', false);
    }

    // ---------------------------------------------------------------
    // Estimated Minutes
    // ---------------------------------------------------------------

    public function test_store_with_estimated_minutes(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('tasks.store'), [
            'title' => 'Estimated Task',
            'status' => 'pending',
            'priority' => 'medium',
            'due_date' => now()->addDay()->format('Y-m-d'),
            'estimated_minutes' => 45,
        ]);

        $response->assertRedirect(route('tasks.index'));
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('tasks', [
            'title' => 'Estimated Task',
            'estimated_minutes' => 45,
        ]);
    }

    public function test_store_rejects_estimated_minutes_below_min(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('tasks.store'), [
            'title' => 'Test',
            'status' => 'pending',
            'priority' => 'low',
            'due_date' => now()->addDay()->format('Y-m-d'),
            'estimated_minutes' => 0,
        ]);

        $response->assertSessionHasErrors('estimated_minutes');
    }

    public function test_store_rejects_estimated_minutes_above_max(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('tasks.store'), [
            'title' => 'Test',
            'status' => 'pending',
            'priority' => 'low',
            'due_date' => now()->addDay()->format('Y-m-d'),
            'estimated_minutes' => 481,
        ]);

        $response->assertSessionHasErrors('estimated_minutes');
    }

    public function test_store_allows_null_estimated_minutes(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('tasks.store'), [
            'title' => 'No Estimate Task',
            'status' => 'pending',
            'priority' => 'low',
            'due_date' => now()->addDay()->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('tasks.index'));
        $response->assertSessionHasNoErrors();
    }

    public function test_update_with_estimated_minutes(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create();

        $response = $this->actingAs($user)->put(route('tasks.update', $task), [
            'title' => $task->title,
            'status' => 'pending',
            'priority' => 'low',
            'due_date' => now()->addDay()->format('Y-m-d'),
            'estimated_minutes' => 120,
        ]);

        $response->assertRedirect(route('tasks.show', $task));
        $response->assertSessionHasNoErrors();

        $this->assertEquals(120, $task->fresh()->estimated_minutes);
    }

    // ---------------------------------------------------------------
    // Model: formattedEstimate()
    // ---------------------------------------------------------------

    public function test_formatted_estimate_minutes_only(): void
    {
        $task = Task::factory()->create(['estimated_minutes' => 30]);

        $this->assertEquals('30m', $task->formattedEstimate());
    }

    public function test_formatted_estimate_hours_only(): void
    {
        $task = Task::factory()->create(['estimated_minutes' => 120]);

        $this->assertEquals('2h', $task->formattedEstimate());
    }

    public function test_formatted_estimate_hours_and_minutes(): void
    {
        $task = Task::factory()->create(['estimated_minutes' => 90]);

        $this->assertEquals('1h 30m', $task->formattedEstimate());
    }

    public function test_formatted_estimate_null(): void
    {
        $task = Task::factory()->create(['estimated_minutes' => null]);

        $this->assertNull($task->formattedEstimate());
    }

    // ---------------------------------------------------------------
    // Additional Validation Edge Cases
    // ---------------------------------------------------------------

    public function test_store_rejects_empty_string_title(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('tasks.store'), [
            'title' => '',
            'status' => 'pending',
            'priority' => 'low',
            'due_date' => now()->addDay()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('title');
    }

    public function test_store_rejects_non_string_title(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('tasks.store'), [
            'title' => 12345,
            'status' => 'pending',
            'priority' => 'low',
            'due_date' => now()->addDay()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('title');
    }

    public function test_store_rejects_non_string_description(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('tasks.store'), [
            'title' => 'Test',
            'description' => ['array', 'not', 'string'],
            'status' => 'pending',
            'priority' => 'low',
            'due_date' => now()->addDay()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('description');
    }

    public function test_update_title_max_length_rejected(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create();

        $response = $this->actingAs($user)->put(route('tasks.update', $task), [
            'title' => str_repeat('a', 256),
            'status' => 'pending',
            'priority' => 'low',
            'due_date' => now()->addDay()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('title');
    }

    public function test_update_description_max_length_rejected(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create();

        $response = $this->actingAs($user)->put(route('tasks.update', $task), [
            'title' => 'Test',
            'description' => str_repeat('a', 5001),
            'status' => 'pending',
            'priority' => 'low',
            'due_date' => now()->addDay()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('description');
    }

    public function test_update_allows_past_due_date(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->overdue()->for($user)->create();

        $response = $this->actingAs($user)->put(route('tasks.update', $task), [
            'title' => 'Updated overdue task',
            'status' => 'pending',
            'priority' => 'high',
            'due_date' => now()->subDays(3)->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('tasks.show', $task));
        $response->assertSessionHasNoErrors();
    }

    public function test_store_non_recurring_clears_recurring_times(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('tasks.store'), [
            'title' => 'Non Recurring Task',
            'status' => 'pending',
            'priority' => 'low',
            'due_date' => now()->addDay()->format('Y-m-d'),
            'is_recurring_daily' => '0',
            'recurring_times' => ['09:00'],
        ]);

        $response->assertRedirect(route('tasks.index'));

        $task = Task::where('title', 'Non Recurring Task')->first();
        $this->assertNotNull($task);
        $this->assertNull($task->recurring_times);
    }

    public function test_update_rejects_empty_string_title(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create();

        $response = $this->actingAs($user)->put(route('tasks.update', $task), [
            'title' => '',
            'status' => 'pending',
            'priority' => 'low',
            'due_date' => now()->addDay()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('title');
    }

    // ---------------------------------------------------------------
    // Additional Model Behavior
    // ---------------------------------------------------------------

    public function test_store_with_completed_status_auto_sets_completed_at(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('tasks.store'), [
            'title' => 'Completed on create',
            'status' => 'completed',
            'priority' => 'low',
            'due_date' => now()->addDay()->format('Y-m-d'),
        ]);

        $task = Task::where('title', 'Completed on create')->first();
        $this->assertNotNull($task);
        $this->assertNotNull($task->completed_at);
    }

    public function test_completed_at_not_overwritten_when_already_set(): void
    {
        $user = User::factory()->create();
        $originalCompletedAt = now()->subDay();

        $task = Task::factory()->for($user)->create([
            'status' => 'completed',
            'completed_at' => $originalCompletedAt,
            'due_date' => now()->addDay()->format('Y-m-d'),
        ]);

        $task->update(['title' => 'Updated title']);

        $this->assertEquals(
            $originalCompletedAt->startOfSecond()->timestamp,
            $task->fresh()->completed_at->startOfSecond()->timestamp
        );
    }

    public function test_model_casts_recurring_times_to_array(): void
    {
        $task = Task::factory()->recurringDaily()->create([
            'recurring_times' => ['09:00', '14:30'],
        ]);

        $this->assertIsArray($task->recurring_times);
        $this->assertCount(2, $task->recurring_times);
    }

    public function test_model_casts_completed_at_to_datetime(): void
    {
        $task = Task::factory()->completed()->create();

        $this->assertInstanceOf(\DateTimeInterface::class, $task->completed_at);
    }

    // ---------------------------------------------------------------
    // Additional View Assertions
    // ---------------------------------------------------------------

    public function test_show_displays_no_description_message(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create(['description' => null]);

        $response = $this->actingAs($user)->get(route('tasks.show', $task));

        $response->assertOk();
        $response->assertSeeText('No description provided.');
    }

    public function test_show_displays_no_due_date_message(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create([
            'due_date' => null,
            'is_recurring_daily' => false,
        ]);

        $response = $this->actingAs($user)->get(route('tasks.show', $task));

        $response->assertOk();
        $response->assertSeeText('No due date set');
    }

    public function test_show_displays_timestamps(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create();

        $response = $this->actingAs($user)->get(route('tasks.show', $task));

        $response->assertOk();
        $response->assertSeeText('Created');
        $response->assertSeeText('Last Updated');
    }

    public function test_edit_form_shows_current_values(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create([
            'title' => 'My Editable Task',
            'description' => 'Some description text',
            'priority' => 'high',
        ]);

        $response = $this->actingAs($user)->get(route('tasks.edit', $task));

        $response->assertOk();
        $response->assertSee('My Editable Task');
        $response->assertSee('Some description text');
    }

    public function test_index_error_message_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->withSession(['error' => 'Something went wrong.'])
            ->get(route('tasks.index'));

        $response->assertOk();
        $response->assertSeeText('Something went wrong.');
    }

    // ---------------------------------------------------------------
    // Additional Security Tests
    // ---------------------------------------------------------------

    public function test_user_cannot_set_user_id_via_update(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $task = Task::factory()->for($user)->create();

        $this->actingAs($user)->put(route('tasks.update', $task), [
            'title' => 'Sneaky Update',
            'status' => 'pending',
            'priority' => 'low',
            'due_date' => now()->addDay()->format('Y-m-d'),
            'user_id' => $otherUser->id,
        ]);

        $this->assertEquals($user->id, $task->fresh()->user_id);
    }

    public function test_store_title_with_html_is_escaped_on_index(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('tasks.store'), [
            'title' => '<script>alert("xss")</script>',
            'status' => 'pending',
            'priority' => 'low',
            'due_date' => now()->addDay()->format('Y-m-d'),
        ]);

        $response = $this->actingAs($user)->get(route('tasks.index'));

        $response->assertOk();
        $response->assertDontSee('<script>alert("xss")</script>', false);
        $response->assertSee('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;', false);
    }

    public function test_store_description_with_html_is_escaped_on_show(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('tasks.store'), [
            'title' => 'XSS Test',
            'description' => '<img src=x onerror=alert(1)>',
            'status' => 'pending',
            'priority' => 'low',
            'due_date' => now()->addDay()->format('Y-m-d'),
        ]);

        $task = Task::where('title', 'XSS Test')->first();
        $response = $this->actingAs($user)->get(route('tasks.show', $task));

        $response->assertOk();
        $response->assertDontSee('<img src=x onerror=alert(1)>', false);
    }

    // ---------------------------------------------------------------
    // Additional Update Edge Cases
    // ---------------------------------------------------------------

    public function test_update_due_date_required_for_non_recurring(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create();

        $response = $this->actingAs($user)->put(route('tasks.update', $task), [
            'title' => 'Test',
            'status' => 'pending',
            'priority' => 'low',
        ]);

        $response->assertSessionHasErrors('due_date');
    }

    public function test_update_recurring_task_with_invalid_time_format_fails(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create();

        $response = $this->actingAs($user)->put(route('tasks.update', $task), [
            'title' => 'Test',
            'priority' => 'low',
            'is_recurring_daily' => '1',
            'recurring_times' => ['not-a-time'],
        ]);

        $response->assertSessionHasErrors('recurring_times.0');
    }

    public function test_update_recurring_task_rejects_duplicate_times(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create();

        $response = $this->actingAs($user)->put(route('tasks.update', $task), [
            'title' => 'Test',
            'priority' => 'low',
            'is_recurring_daily' => '1',
            'recurring_times' => ['09:00', '09:00'],
        ]);

        $response->assertSessionHasErrors('recurring_times.1');
    }

    public function test_update_recurring_task_with_multiple_times(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create();

        $response = $this->actingAs($user)->put(route('tasks.update', $task), [
            'title' => 'Multi-time recurring',
            'priority' => 'medium',
            'is_recurring_daily' => '1',
            'recurring_times' => ['08:00', '12:00', '17:00'],
        ]);

        $response->assertRedirect(route('tasks.show', $task));
        $response->assertSessionHasNoErrors();

        $task->refresh();
        $this->assertEquals(['08:00', '12:00', '17:00'], $task->recurring_times);
    }

    public function test_update_accepts_all_valid_statuses(): void
    {
        $user = User::factory()->create();

        foreach (Task::STATUSES as $status) {
            $task = Task::factory()->for($user)->create();

            $response = $this->actingAs($user)->put(route('tasks.update', $task), [
                'title' => "Updated to {$status}",
                'status' => $status,
                'priority' => 'low',
                'due_date' => now()->addDay()->format('Y-m-d'),
            ]);

            $response->assertSessionHasNoErrors();
        }
    }

    public function test_update_accepts_all_valid_priorities(): void
    {
        $user = User::factory()->create();

        foreach (Task::PRIORITIES as $priority) {
            $task = Task::factory()->for($user)->create();

            $response = $this->actingAs($user)->put(route('tasks.update', $task), [
                'title' => "Updated to {$priority}",
                'status' => 'pending',
                'priority' => $priority,
                'due_date' => now()->addDay()->format('Y-m-d'),
            ]);

            $response->assertSessionHasNoErrors();
        }
    }

    // ---------------------------------------------------------------
    // Additional Priority Label Coverage
    // ---------------------------------------------------------------

    public function test_all_priority_labels_are_defined(): void
    {
        $expectedLabels = [
            'low' => 'Low',
            'medium' => 'Medium',
            'high' => 'High',
            'urgent' => 'Urgent',
        ];

        foreach ($expectedLabels as $priority => $label) {
            $task = Task::factory()->create(['priority' => $priority]);
            $this->assertEquals($label, $task->priorityLabel());
        }
    }

    // ---------------------------------------------------------------
    // Query Scopes
    // ---------------------------------------------------------------

    public function test_scope_overdue_returns_past_due_incomplete_tasks(): void
    {
        $user = User::factory()->create();

        $overdueTask = Task::factory()->overdue()->for($user)->create(['title' => 'Overdue']);
        Task::factory()->completed()->for($user)->create(['title' => 'Completed']);
        Task::factory()->for($user)->create(['title' => 'Future', 'due_date' => now()->addDays(5)->format('Y-m-d')]);

        $results = $user->tasks()->overdue()->pluck('title');

        $this->assertContains('Overdue', $results);
        $this->assertNotContains('Completed', $results);
        $this->assertNotContains('Future', $results);
    }

    public function test_scope_incomplete_excludes_completed(): void
    {
        $user = User::factory()->create();

        Task::factory()->for($user)->create(['title' => 'Pending', 'status' => 'pending']);
        Task::factory()->for($user)->create(['title' => 'In Progress', 'status' => 'in_progress']);
        Task::factory()->completed()->for($user)->create(['title' => 'Done']);

        $results = $user->tasks()->incomplete()->pluck('title');

        $this->assertContains('Pending', $results);
        $this->assertContains('In Progress', $results);
        $this->assertNotContains('Done', $results);
    }

    public function test_scope_search_matches_title_and_description(): void
    {
        $user = User::factory()->create();

        Task::factory()->for($user)->create(['title' => 'Buy milk', 'description' => 'From the store']);
        Task::factory()->for($user)->create(['title' => 'Write code', 'description' => 'Buy some coffee']);
        Task::factory()->for($user)->create(['title' => 'Exercise', 'description' => 'Go for a run']);

        $results = $user->tasks()->search('buy')->pluck('title');

        $this->assertContains('Buy milk', $results);
        $this->assertContains('Write code', $results);
        $this->assertNotContains('Exercise', $results);
    }

    // ---------------------------------------------------------------
    // Schedule Status
    // ---------------------------------------------------------------

    public function test_schedule_status_pending(): void
    {
        $task = Task::factory()->create([
            'status' => 'pending',
            'due_date' => now()->addDays(5)->format('Y-m-d'),
        ]);

        $this->assertEquals('pending', $task->scheduleStatus());
        $this->assertEquals('Pending', $task->scheduleStatusLabel());
    }

    public function test_schedule_status_missed(): void
    {
        $task = Task::factory()->overdue()->create();

        $this->assertEquals('missed', $task->scheduleStatus());
        $this->assertEquals('Missed', $task->scheduleStatusLabel());
    }

    public function test_schedule_status_completed_on_time(): void
    {
        $task = Task::factory()->completed()->create();

        $this->assertEquals('completed_on_time', $task->scheduleStatus());
        $this->assertEquals('On Time', $task->scheduleStatusLabel());
    }

    public function test_schedule_status_completed_late(): void
    {
        $task = Task::factory()->completedLate()->create();

        $this->assertEquals('completed_late', $task->scheduleStatus());
        $this->assertEquals('Completed Late', $task->scheduleStatusLabel());
    }

    // ---------------------------------------------------------------
    // Index: Estimated Time Column
    // ---------------------------------------------------------------

    public function test_index_shows_estimated_time(): void
    {
        $user = User::factory()->create();
        Task::factory()->withEstimate(60)->for($user)->create(['title' => 'Hour Task']);

        $response = $this->actingAs($user)->get(route('tasks.index'));

        $response->assertOk();
        $response->assertSeeText('1h');
    }

    public function test_index_shows_dash_for_no_estimate(): void
    {
        $user = User::factory()->create();
        Task::factory()->for($user)->create(['estimated_minutes' => null]);

        $response = $this->actingAs($user)->get(route('tasks.index'));

        $response->assertOk();
    }

    // ---------------------------------------------------------------
    // Edit form shows estimate
    // ---------------------------------------------------------------

    public function test_edit_form_shows_estimated_minutes(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->withEstimate(45)->for($user)->create();

        $response = $this->actingAs($user)->get(route('tasks.edit', $task));

        $response->assertOk();
        $response->assertSee('Estimated Time');
        $response->assertSee('45');
    }

    public function test_create_form_shows_estimate_field(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('tasks.create'));

        $response->assertOk();
        $response->assertSee('data-test="task-estimate-input"', false);
    }
}
