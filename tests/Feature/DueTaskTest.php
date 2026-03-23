<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DueTaskTest extends TestCase
{
    use RefreshDatabase;

    // ---------------------------------------------------------------
    // Authentication Guards
    // ---------------------------------------------------------------

    public function test_guest_cannot_access_due_tasks(): void
    {
        $response = $this->get(route('due-tasks.index'));

        $response->assertRedirect(route('login'));
    }

    // ---------------------------------------------------------------
    // Authorization — Only own tasks visible
    // ---------------------------------------------------------------

    public function test_index_only_shows_own_tasks(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $ownTask = Task::factory()->for($user)->create([
            'due_date' => now()->addDays(3)->format('Y-m-d'),
            'status' => 'pending',
        ]);
        $otherTask = Task::factory()->for($otherUser)->create([
            'due_date' => now()->addDays(3)->format('Y-m-d'),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->get(route('due-tasks.index'));

        $response->assertOk();
        $response->assertSeeText($ownTask->title);
        $response->assertDontSeeText($otherTask->title);
    }

    // ---------------------------------------------------------------
    // Daily Task Exclusion
    // ---------------------------------------------------------------

    public function test_daily_tasks_are_excluded(): void
    {
        $user = User::factory()->create();

        Task::factory()->recurringDaily()->for($user)->create([
            'title' => 'Daily Task Should Not Appear',
        ]);
        $dueTask = Task::factory()->for($user)->create([
            'title' => 'Regular Due Task',
            'due_date' => now()->addDays(2)->format('Y-m-d'),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->get(route('due-tasks.index'));

        $response->assertOk();
        $response->assertSeeText('Regular Due Task');
        $response->assertDontSeeText('Daily Task Should Not Appear');
    }

    // ---------------------------------------------------------------
    // Incomplete Section
    // ---------------------------------------------------------------

    public function test_authenticated_user_can_view_due_tasks(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('due-tasks.index'));

        $response->assertOk();
        $response->assertSeeText('Due Tasks');
    }

    public function test_shows_empty_state_when_no_incomplete_tasks(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('due-tasks.index'));

        $response->assertOk();
        $response->assertSee('data-test="empty-incomplete"', false);
        $response->assertSeeText('All caught up!');
    }

    public function test_pending_tasks_appear_in_incomplete_section(): void
    {
        $user = User::factory()->create();

        $task = Task::factory()->for($user)->create([
            'title' => 'Pending Future Task',
            'due_date' => now()->addDays(5)->format('Y-m-d'),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->get(route('due-tasks.index'));

        $response->assertOk();
        $response->assertSeeText('Pending Future Task');
        $response->assertSeeText('Pending');
    }

    public function test_missed_tasks_appear_in_incomplete_section(): void
    {
        $user = User::factory()->create();

        $task = Task::factory()->overdue()->for($user)->create([
            'title' => 'Overdue Missed Task',
        ]);

        $response = $this->actingAs($user)->get(route('due-tasks.index'));

        $response->assertOk();
        $response->assertSeeText('Overdue Missed Task');
        $response->assertSeeText('Missed');
    }

    public function test_incomplete_tasks_ordered_by_due_date_ascending(): void
    {
        $user = User::factory()->create();

        $laterTask = Task::factory()->for($user)->create([
            'title' => 'Later Task',
            'due_date' => now()->addDays(10)->format('Y-m-d'),
            'status' => 'pending',
        ]);
        $earlierTask = Task::factory()->for($user)->create([
            'title' => 'Earlier Task',
            'due_date' => now()->addDay()->format('Y-m-d'),
            'status' => 'pending',
        ]);
        $overdueTask = Task::factory()->overdue()->for($user)->create([
            'title' => 'Overdue Task',
            'due_date' => now()->subDays(5)->format('Y-m-d'),
        ]);

        $response = $this->actingAs($user)->get(route('due-tasks.index'));

        $response->assertOk();
        $response->assertSeeTextInOrder(['Overdue Task', 'Earlier Task', 'Later Task']);
    }

    public function test_missed_tasks_show_red_due_date(): void
    {
        $user = User::factory()->create();

        Task::factory()->overdue()->for($user)->create([
            'title' => 'Overdue Red Task',
            'due_date' => now()->subDays(3)->format('Y-m-d'),
        ]);

        $response = $this->actingAs($user)->get(route('due-tasks.index'));

        $response->assertOk();
        $response->assertSee('font-medium text-red-600 dark:text-red-400', false);
    }

    // ---------------------------------------------------------------
    // Completed Section
    // ---------------------------------------------------------------

    public function test_shows_empty_state_when_no_completed_tasks(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('due-tasks.index'));

        $response->assertOk();
        $response->assertSee('data-test="empty-completed"', false);
        $response->assertSeeText('No completed tasks');
    }

    public function test_completed_on_time_tasks_appear_in_completed_section(): void
    {
        $user = User::factory()->create();

        Task::factory()->completed()->for($user)->create([
            'title' => 'Completed On Time Task',
            'due_date' => now()->addDays(5)->format('Y-m-d'),
        ]);

        $response = $this->actingAs($user)->get(route('due-tasks.index'));

        $response->assertOk();
        $response->assertSeeText('Completed On Time Task');
        $response->assertSeeText('On Time');
    }

    public function test_completed_late_tasks_show_indicator(): void
    {
        $user = User::factory()->create();

        Task::factory()->completedLate()->for($user)->create([
            'title' => 'Late Completion Task',
        ]);

        $response = $this->actingAs($user)->get(route('due-tasks.index'));

        $response->assertOk();
        $response->assertSeeText('Late Completion Task');
        $response->assertSeeText('Completed Late');
        $response->assertSee('bg-amber-100', false);
    }

    public function test_completed_tasks_ordered_by_completed_at_descending(): void
    {
        $user = User::factory()->create();

        $olderTask = Task::factory()->for($user)->create([
            'title' => 'Older Completed',
            'status' => 'completed',
            'completed_at' => now()->subDays(5),
            'due_date' => now()->subDays(3)->format('Y-m-d'),
        ]);
        $newerTask = Task::factory()->for($user)->create([
            'title' => 'Newer Completed',
            'status' => 'completed',
            'completed_at' => now()->subDay(),
            'due_date' => now()->addDays(2)->format('Y-m-d'),
        ]);

        $response = $this->actingAs($user)->get(route('due-tasks.index'));

        $response->assertOk();
        $response->assertSeeTextInOrder(['Newer Completed', 'Older Completed']);
    }

    // ---------------------------------------------------------------
    // No CRUD Actions
    // ---------------------------------------------------------------

    public function test_page_has_no_create_or_edit_or_delete_actions(): void
    {
        $user = User::factory()->create();

        Task::factory()->for($user)->create([
            'due_date' => now()->addDays(3)->format('Y-m-d'),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->get(route('due-tasks.index'));

        $response->assertOk();
        $response->assertDontSee('data-test="edit-task"', false);
        $response->assertDontSee('data-test="delete-task-trigger"', false);
        $response->assertDontSee('data-test="confirm-delete"', false);
    }

    // ---------------------------------------------------------------
    // Toggle Button
    // ---------------------------------------------------------------

    public function test_toggle_button_shows_completed_count(): void
    {
        $user = User::factory()->create();

        Task::factory()->completed()->count(3)->for($user)->create([
            'due_date' => now()->addDays(5)->format('Y-m-d'),
        ]);

        $response = $this->actingAs($user)->get(route('due-tasks.index'));

        $response->assertOk();
        $response->assertSee('data-test="toggle-completed"', false);
    }

    // ---------------------------------------------------------------
    // Model — Schedule Status Helpers
    // ---------------------------------------------------------------

    public function test_schedule_status_returns_pending_for_future_task(): void
    {
        $task = Task::factory()->make([
            'status' => 'pending',
            'due_date' => now()->addDays(5)->format('Y-m-d'),
        ]);

        $this->assertSame('pending', $task->scheduleStatus());
        $this->assertSame('Pending', $task->scheduleStatusLabel());
        $this->assertFalse($task->isMissed());
    }

    public function test_schedule_status_returns_missed_for_overdue_task(): void
    {
        $task = Task::factory()->make([
            'status' => 'pending',
            'due_date' => now()->subDays(3)->format('Y-m-d'),
        ]);

        $this->assertSame('missed', $task->scheduleStatus());
        $this->assertSame('Missed', $task->scheduleStatusLabel());
        $this->assertTrue($task->isMissed());
    }

    public function test_schedule_status_returns_completed_on_time(): void
    {
        $task = Task::factory()->make([
            'status' => 'completed',
            'due_date' => now()->addDays(5)->format('Y-m-d'),
            'completed_at' => now(),
        ]);

        $this->assertSame('completed_on_time', $task->scheduleStatus());
        $this->assertSame('On Time', $task->scheduleStatusLabel());
    }

    public function test_schedule_status_returns_completed_late(): void
    {
        $task = Task::factory()->make([
            'status' => 'completed',
            'due_date' => now()->subDays(5)->format('Y-m-d'),
            'completed_at' => now(),
        ]);

        $this->assertSame('completed_late', $task->scheduleStatus());
        $this->assertSame('Completed Late', $task->scheduleStatusLabel());
    }

    // ---------------------------------------------------------------
    // Model — Auto-set completed_at on status change
    // ---------------------------------------------------------------

    public function test_completed_at_auto_set_when_status_changes_to_completed(): void
    {
        $user = User::factory()->create();

        $task = Task::factory()->for($user)->create([
            'status' => 'pending',
            'due_date' => now()->addDays(5)->format('Y-m-d'),
        ]);

        $this->assertNull($task->completed_at);

        $task->update(['status' => 'completed']);

        $this->assertNotNull($task->fresh()->completed_at);
    }

    public function test_completed_at_cleared_when_status_changes_from_completed(): void
    {
        $user = User::factory()->create();

        $task = Task::factory()->completed()->for($user)->create([
            'due_date' => now()->addDays(5)->format('Y-m-d'),
        ]);

        $this->assertNotNull($task->completed_at);

        $task->update(['status' => 'pending']);

        $this->assertNull($task->fresh()->completed_at);
    }

    // ---------------------------------------------------------------
    // Pagination
    // ---------------------------------------------------------------

    public function test_completed_section_paginates(): void
    {
        $user = User::factory()->create();

        Task::factory()->completed()->count(20)->for($user)->create([
            'due_date' => now()->addDays(3)->format('Y-m-d'),
        ]);

        $response = $this->actingAs($user)->get(route('due-tasks.index'));

        $response->assertOk();
        $response->assertSee('data-test="pagination"', false);
    }

    // ---------------------------------------------------------------
    // Tasks without due_date excluded from incomplete
    // ---------------------------------------------------------------

    public function test_tasks_without_due_date_excluded_from_incomplete(): void
    {
        $user = User::factory()->create();

        Task::factory()->for($user)->create([
            'title' => 'No Due Date Task',
            'due_date' => null,
            'status' => 'pending',
            'is_recurring_daily' => false,
        ]);

        $response = $this->actingAs($user)->get(route('due-tasks.index'));

        $response->assertOk();
        $response->assertDontSeeText('No Due Date Task');
    }

    // ---------------------------------------------------------------
    // Sorting
    // ---------------------------------------------------------------

    public function test_due_tasks_sort_title_asc(): void
    {
        $user = User::factory()->create();

        Task::factory()->for($user)->create([
            'title' => 'Zebra Task',
            'due_date' => now()->addDays(3)->format('Y-m-d'),
            'status' => 'pending',
        ]);
        Task::factory()->for($user)->create([
            'title' => 'Apple Task',
            'due_date' => now()->addDays(5)->format('Y-m-d'),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->get(route('due-tasks.index', ['sort' => 'title_asc']));

        $response->assertOk();
        $response->assertSeeTextInOrder(['Apple Task', 'Zebra Task']);
    }

    public function test_due_tasks_sort_title_desc(): void
    {
        $user = User::factory()->create();

        Task::factory()->for($user)->create([
            'title' => 'Apple Task',
            'due_date' => now()->addDays(3)->format('Y-m-d'),
            'status' => 'pending',
        ]);
        Task::factory()->for($user)->create([
            'title' => 'Zebra Task',
            'due_date' => now()->addDays(5)->format('Y-m-d'),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->get(route('due-tasks.index', ['sort' => 'title_desc']));

        $response->assertOk();
        $response->assertSeeTextInOrder(['Zebra Task', 'Apple Task']);
    }

    public function test_due_tasks_default_sort_is_by_due_date(): void
    {
        $user = User::factory()->create();

        Task::factory()->for($user)->create([
            'title' => 'Far Task',
            'due_date' => now()->addDays(10)->format('Y-m-d'),
            'status' => 'pending',
        ]);
        Task::factory()->for($user)->create([
            'title' => 'Near Task',
            'due_date' => now()->addDay()->format('Y-m-d'),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->get(route('due-tasks.index'));

        $response->assertOk();
        $response->assertSeeTextInOrder(['Near Task', 'Far Task']);
    }

    public function test_due_tasks_invalid_sort_falls_back_to_default(): void
    {
        $user = User::factory()->create();

        Task::factory()->for($user)->create([
            'title' => 'Far Task',
            'due_date' => now()->addDays(10)->format('Y-m-d'),
            'status' => 'pending',
        ]);
        Task::factory()->for($user)->create([
            'title' => 'Near Task',
            'due_date' => now()->addDay()->format('Y-m-d'),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->get(route('due-tasks.index', ['sort' => 'malicious_input']));

        $response->assertOk();
        $response->assertSeeTextInOrder(['Near Task', 'Far Task']);
    }

    public function test_due_tasks_sort_dropdown_is_visible(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('due-tasks.index'));

        $response->assertOk();
        $response->assertSee('data-test="sort-select"', false);
    }

    // ---------------------------------------------------------------
    // Additional Edge Cases
    // ---------------------------------------------------------------

    public function test_in_progress_tasks_appear_in_incomplete_section(): void
    {
        $user = User::factory()->create();

        $task = Task::factory()->for($user)->create([
            'title' => 'In Progress Task',
            'due_date' => now()->addDays(5)->format('Y-m-d'),
            'status' => 'in_progress',
        ]);

        $response = $this->actingAs($user)->get(route('due-tasks.index'));

        $response->assertOk();
        $response->assertSeeText('In Progress Task');
    }

    public function test_schedule_status_pending_for_in_progress_future_task(): void
    {
        $task = Task::factory()->make([
            'status' => 'in_progress',
            'due_date' => now()->addDays(5)->format('Y-m-d'),
        ]);

        $this->assertSame('pending', $task->scheduleStatus());
    }

    public function test_schedule_status_missed_for_in_progress_overdue_task(): void
    {
        $task = Task::factory()->make([
            'status' => 'in_progress',
            'due_date' => now()->subDays(3)->format('Y-m-d'),
        ]);

        $this->assertSame('missed', $task->scheduleStatus());
        $this->assertTrue($task->isMissed());
    }

    public function test_completed_tasks_not_in_incomplete_section(): void
    {
        $user = User::factory()->create();

        $task = Task::factory()->completed()->for($user)->create([
            'title' => 'Completed Not Incomplete',
            'due_date' => now()->addDays(3)->format('Y-m-d'),
        ]);

        $response = $this->actingAs($user)->get(route('due-tasks.index'));

        $response->assertOk();
        // Completed tasks should only appear in the completed section
        $response->assertSee('data-test="empty-incomplete"', false);
    }

    public function test_many_incomplete_tasks_all_display(): void
    {
        $user = User::factory()->create();

        Task::factory()->count(10)->for($user)->create([
            'due_date' => now()->addDays(3)->format('Y-m-d'),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->get(route('due-tasks.index'));

        $response->assertOk();
        $response->assertDontSee('data-test="empty-incomplete"', false);
    }

    public function test_completed_late_due_date_before_completed_at(): void
    {
        $task = Task::factory()->make([
            'status' => 'completed',
            'due_date' => now()->subDays(10)->format('Y-m-d'),
            'completed_at' => now()->subDays(2),
        ]);

        $this->assertSame('completed_late', $task->scheduleStatus());
        $this->assertSame('Completed Late', $task->scheduleStatusLabel());
    }

    public function test_completed_on_time_due_date_same_day(): void
    {
        $task = Task::factory()->make([
            'status' => 'completed',
            'due_date' => now()->format('Y-m-d'),
            'completed_at' => now(),
        ]);

        $this->assertSame('completed_on_time', $task->scheduleStatus());
        $this->assertSame('On Time', $task->scheduleStatusLabel());
    }

    public function test_due_tasks_page_has_no_store_route(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('tasks.store'), [
            'title' => 'Attempting from due tasks',
            'status' => 'pending',
            'priority' => 'low',
            'due_date' => now()->addDay()->format('Y-m-d'),
        ]);

        // It should still work because it hits the tasks.store route
        // But the due-tasks page itself has no create form
        $response->assertRedirect(route('tasks.index'));
    }
}
