<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkspaceTest extends TestCase
{
    use RefreshDatabase;

    // ---------------------------------------------------------------
    // Authentication Guards
    // ---------------------------------------------------------------

    public function test_guest_cannot_access_workspace_index(): void
    {
        $response = $this->get(route('workspaces.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_access_workspace_create(): void
    {
        $response = $this->get(route('workspaces.create'));

        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_store_workspace(): void
    {
        $response = $this->post(route('workspaces.store'), [
            'name' => 'Engineering',
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_view_workspace(): void
    {
        $workspace = Workspace::factory()->create();

        $response = $this->get(route('workspaces.show', $workspace));

        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_edit_workspace(): void
    {
        $workspace = Workspace::factory()->create();

        $response = $this->get(route('workspaces.edit', $workspace));

        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_update_workspace(): void
    {
        $workspace = Workspace::factory()->create();

        $response = $this->put(route('workspaces.update', $workspace), [
            'name' => 'Updated',
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_delete_workspace(): void
    {
        $workspace = Workspace::factory()->create();

        $response = $this->delete(route('workspaces.destroy', $workspace));

        $response->assertRedirect(route('login'));
    }

    // ---------------------------------------------------------------
    // Authorization — Users cannot access other users' workspaces
    // ---------------------------------------------------------------

    public function test_user_cannot_view_another_users_workspace(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $workspace = Workspace::factory()->for($otherUser)->create();

        $response = $this->actingAs($user)->get(route('workspaces.show', $workspace));

        $response->assertForbidden();
    }

    public function test_user_cannot_edit_another_users_workspace(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $workspace = Workspace::factory()->for($otherUser)->create();

        $response = $this->actingAs($user)->get(route('workspaces.edit', $workspace));

        $response->assertForbidden();
    }

    public function test_user_cannot_update_another_users_workspace(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $workspace = Workspace::factory()->for($otherUser)->create();

        $response = $this->actingAs($user)->put(route('workspaces.update', $workspace), [
            'name' => 'Hacked',
        ]);

        $response->assertForbidden();
    }

    public function test_user_cannot_delete_another_users_workspace(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $workspace = Workspace::factory()->for($otherUser)->create();

        $response = $this->actingAs($user)->delete(route('workspaces.destroy', $workspace));

        $response->assertForbidden();
    }

    public function test_index_only_shows_own_workspaces(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Workspace::factory()->for($user)->create(['name' => 'My Workspace']);
        Workspace::factory()->for($otherUser)->create(['name' => 'Other Workspace']);

        $response = $this->actingAs($user)->get(route('workspaces.index'));

        $response->assertOk();
        $response->assertSeeText('My Workspace');
        $response->assertDontSeeText('Other Workspace');
    }

    // ---------------------------------------------------------------
    // Index
    // ---------------------------------------------------------------

    public function test_authenticated_user_can_view_workspace_index(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('workspaces.index'));

        $response->assertOk();
        $response->assertSeeText('Workspaces');
    }

    public function test_index_shows_empty_state_when_no_workspaces(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('workspaces.index'));

        $response->assertOk();
        $response->assertSeeText('No workspaces yet');
    }

    public function test_index_displays_workspaces_with_task_count(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->for($user)->create(['name' => 'Engineering']);
        Task::factory()->count(3)->for($user)->create(['workspace_id' => $workspace->id]);

        $response = $this->actingAs($user)->get(route('workspaces.index'));

        $response->assertOk();
        $response->assertSeeText('Engineering');
        $response->assertSeeText('3 tasks');
    }

    public function test_index_paginates_workspaces(): void
    {
        $user = User::factory()->create();
        Workspace::factory()->count(20)->for($user)->create();

        $response = $this->actingAs($user)->get(route('workspaces.index'));

        $response->assertOk();
        $response->assertSeeText('Showing');
    }

    // ---------------------------------------------------------------
    // Create & Store
    // ---------------------------------------------------------------

    public function test_user_can_view_create_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('workspaces.create'));

        $response->assertOk();
        $response->assertSeeText('Create Workspace');
    }

    public function test_user_can_create_workspace_with_valid_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('workspaces.store'), [
            'name' => 'Engineering',
        ]);

        $response->assertRedirect(route('workspaces.index'));
        $response->assertSessionHas('success', 'Workspace created successfully.');

        $this->assertDatabaseHas('workspaces', [
            'name' => 'Engineering',
            'user_id' => $user->id,
        ]);
    }

    public function test_workspace_is_assigned_to_authenticated_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('workspaces.store'), [
            'name' => 'My workspace',
        ]);

        $workspace = Workspace::first();
        $this->assertNotNull($workspace);
        $this->assertEquals($user->id, $workspace->user_id);
    }

    public function test_store_requires_name(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('workspaces.store'), []);

        $response->assertSessionHasErrors('name');
    }

    public function test_store_rejects_empty_string_name(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('workspaces.store'), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_store_name_max_length(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('workspaces.store'), [
            'name' => str_repeat('a', 256),
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_store_name_at_max_length_succeeds(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('workspaces.store'), [
            'name' => str_repeat('a', 255),
        ]);

        $response->assertRedirect(route('workspaces.index'));
        $response->assertSessionHasNoErrors();
    }

    public function test_store_rejects_duplicate_name_for_same_user(): void
    {
        $user = User::factory()->create();
        Workspace::factory()->for($user)->create(['name' => 'Engineering']);

        $response = $this->actingAs($user)->post(route('workspaces.store'), [
            'name' => 'Engineering',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_store_allows_duplicate_name_for_different_users(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        Workspace::factory()->for($otherUser)->create(['name' => 'Engineering']);

        $response = $this->actingAs($user)->post(route('workspaces.store'), [
            'name' => 'Engineering',
        ]);

        $response->assertRedirect(route('workspaces.index'));
        $response->assertSessionHasNoErrors();
    }

    // ---------------------------------------------------------------
    // Show
    // ---------------------------------------------------------------

    public function test_user_can_view_own_workspace(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->for($user)->create(['name' => 'My Workspace']);

        $response = $this->actingAs($user)->get(route('workspaces.show', $workspace));

        $response->assertOk();
        $response->assertSeeText('My Workspace');
        $response->assertSeeText('Workspace details');
    }

    public function test_show_displays_workspace_tasks(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->for($user)->create();
        $task = Task::factory()->for($user)->create([
            'workspace_id' => $workspace->id,
            'title' => 'Workspace Task',
        ]);

        $response = $this->actingAs($user)->get(route('workspaces.show', $workspace));

        $response->assertOk();
        $response->assertSeeText('Workspace Task');
    }

    public function test_show_displays_empty_state_when_no_tasks(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->for($user)->create();

        $response = $this->actingAs($user)->get(route('workspaces.show', $workspace));

        $response->assertOk();
        $response->assertSeeText('No tasks yet');
    }

    // ---------------------------------------------------------------
    // Edit & Update
    // ---------------------------------------------------------------

    public function test_user_can_view_edit_form_for_own_workspace(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->for($user)->create(['name' => 'Editable']);

        $response = $this->actingAs($user)->get(route('workspaces.edit', $workspace));

        $response->assertOk();
        $response->assertSeeText('Edit Workspace');
        $response->assertSee('Editable');
    }

    public function test_user_can_update_workspace_with_valid_data(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->for($user)->create();

        $response = $this->actingAs($user)->put(route('workspaces.update', $workspace), [
            'name' => 'Updated Name',
        ]);

        $response->assertRedirect(route('workspaces.show', $workspace));
        $response->assertSessionHas('success', 'Workspace updated successfully.');

        $workspace->refresh();
        $this->assertEquals('Updated Name', $workspace->name);
    }

    public function test_update_validates_required_name(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->for($user)->create();

        $response = $this->actingAs($user)->put(route('workspaces.update', $workspace), []);

        $response->assertSessionHasErrors('name');
    }

    public function test_update_rejects_duplicate_name_for_same_user(): void
    {
        $user = User::factory()->create();
        Workspace::factory()->for($user)->create(['name' => 'Engineering']);
        $workspace = Workspace::factory()->for($user)->create(['name' => 'Marketing']);

        $response = $this->actingAs($user)->put(route('workspaces.update', $workspace), [
            'name' => 'Engineering',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_update_allows_keeping_same_name(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->for($user)->create(['name' => 'Engineering']);

        $response = $this->actingAs($user)->put(route('workspaces.update', $workspace), [
            'name' => 'Engineering',
        ]);

        $response->assertRedirect(route('workspaces.show', $workspace));
        $response->assertSessionHasNoErrors();
    }

    // ---------------------------------------------------------------
    // Delete
    // ---------------------------------------------------------------

    public function test_user_can_delete_own_workspace(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->for($user)->create();

        $response = $this->actingAs($user)->delete(route('workspaces.destroy', $workspace));

        $response->assertRedirect(route('workspaces.index'));
        $response->assertSessionHas('success', 'Workspace deleted successfully.');

        $this->assertDatabaseMissing('workspaces', [
            'id' => $workspace->id,
        ]);
    }

    public function test_deleted_workspace_no_longer_appears_in_index(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->for($user)->create(['name' => 'Doomed Workspace']);

        $this->actingAs($user)->delete(route('workspaces.destroy', $workspace));

        $response = $this->actingAs($user)->get(route('workspaces.index'));

        $response->assertDontSeeText('Doomed Workspace');
    }

    public function test_deleting_workspace_nullifies_task_workspace_id(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->for($user)->create();
        $task = Task::factory()->for($user)->create(['workspace_id' => $workspace->id]);

        $this->actingAs($user)->delete(route('workspaces.destroy', $workspace));

        $task->refresh();
        $this->assertNull($task->workspace_id);
    }

    // ---------------------------------------------------------------
    // Model Helpers
    // ---------------------------------------------------------------

    public function test_workspace_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->for($user)->create();

        $this->assertInstanceOf(User::class, $workspace->user);
        $this->assertEquals($user->id, $workspace->user->id);
    }

    public function test_user_has_many_workspaces(): void
    {
        $user = User::factory()->create();
        Workspace::factory()->count(3)->for($user)->create();

        $this->assertCount(3, $user->workspaces);
    }

    public function test_workspace_has_many_tasks(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->for($user)->create();
        Task::factory()->count(2)->for($user)->create(['workspace_id' => $workspace->id]);

        $this->assertCount(2, $workspace->tasks);
    }

    // ---------------------------------------------------------------
    // Cascade Delete
    // ---------------------------------------------------------------

    public function test_deleting_user_deletes_their_workspaces(): void
    {
        $user = User::factory()->create();
        Workspace::factory()->count(3)->for($user)->create();

        $this->assertDatabaseCount('workspaces', 3);

        $user->delete();

        $this->assertDatabaseCount('workspaces', 0);
    }

    // ---------------------------------------------------------------
    // Mass Assignment Protection
    // ---------------------------------------------------------------

    public function test_user_cannot_set_user_id_via_store(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $this->actingAs($user)->post(route('workspaces.store'), [
            'name' => 'Sneaky Workspace',
            'user_id' => $otherUser->id,
        ]);

        $workspace = Workspace::where('name', 'Sneaky Workspace')->first();

        $this->assertNotNull($workspace);
        $this->assertEquals($user->id, $workspace->user_id);
    }

    // ---------------------------------------------------------------
    // Flash Messages
    // ---------------------------------------------------------------

    public function test_success_message_displayed_after_create(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('workspaces.store'), [
            'name' => 'Flash Test',
        ]);

        $response->assertSessionHas('success');

        $indexResponse = $this->actingAs($user)->get(route('workspaces.index'));
        $indexResponse->assertSeeText('Workspace created successfully.');
    }

    public function test_success_message_displayed_after_update(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->for($user)->create();

        $response = $this->actingAs($user)->put(route('workspaces.update', $workspace), [
            'name' => 'Updated',
        ]);

        $response->assertSessionHas('success', 'Workspace updated successfully.');
    }

    public function test_success_message_displayed_after_delete(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->for($user)->create();

        $response = $this->actingAs($user)->delete(route('workspaces.destroy', $workspace));

        $response->assertSessionHas('success', 'Workspace deleted successfully.');
    }

    // ---------------------------------------------------------------
    // Edge Cases
    // ---------------------------------------------------------------

    public function test_nonexistent_workspace_redirects_with_error(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('workspaces.show', 99999));

        $response->assertRedirect(route('workspaces.index'));
        $response->assertSessionHas('error');
    }

    public function test_update_nonexistent_workspace_redirects_with_error(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put(route('workspaces.update', 99999), [
            'name' => 'Test',
        ]);

        $response->assertRedirect(route('workspaces.index'));
        $response->assertSessionHas('error');
    }

    public function test_delete_nonexistent_workspace_redirects_with_error(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->delete(route('workspaces.destroy', 99999));

        $response->assertRedirect(route('workspaces.index'));
        $response->assertSessionHas('error');
    }

    // ---------------------------------------------------------------
    // Sorting
    // ---------------------------------------------------------------

    public function test_index_default_sort_is_name_asc(): void
    {
        $user = User::factory()->create();

        Workspace::factory()->for($user)->create(['name' => 'Zebra']);
        Workspace::factory()->for($user)->create(['name' => 'Apple']);

        $response = $this->actingAs($user)->get(route('workspaces.index'));

        $response->assertOk();
        $response->assertSeeTextInOrder(['Apple', 'Zebra']);
    }

    public function test_index_sort_name_desc(): void
    {
        $user = User::factory()->create();

        Workspace::factory()->for($user)->create(['name' => 'Apple']);
        Workspace::factory()->for($user)->create(['name' => 'Zebra']);

        $response = $this->actingAs($user)->get(route('workspaces.index', ['sort' => 'name_desc']));

        $response->assertOk();
        $response->assertSeeTextInOrder(['Zebra', 'Apple']);
    }

    public function test_index_invalid_sort_falls_back_to_default(): void
    {
        $user = User::factory()->create();

        Workspace::factory()->for($user)->create(['name' => 'Zebra']);
        Workspace::factory()->for($user)->create(['name' => 'Apple']);

        $response = $this->actingAs($user)->get(route('workspaces.index', ['sort' => 'invalid']));

        $response->assertOk();
        $response->assertSeeTextInOrder(['Apple', 'Zebra']);
    }

    public function test_index_sort_dropdown_is_visible(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('workspaces.index'));

        $response->assertOk();
        $response->assertSee('data-test="sort-select"', false);
    }

    // ---------------------------------------------------------------
    // Task-Workspace Integration
    // ---------------------------------------------------------------

    public function test_task_can_be_created_with_workspace(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->for($user)->create();

        $response = $this->actingAs($user)->post(route('tasks.store'), [
            'title' => 'Workspace Task',
            'status' => 'pending',
            'priority' => 'low',
            'due_date' => now()->addDay()->format('Y-m-d'),
            'workspace_id' => $workspace->id,
        ]);

        $response->assertRedirect(route('tasks.index'));
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('tasks', [
            'title' => 'Workspace Task',
            'workspace_id' => $workspace->id,
        ]);
    }

    public function test_task_can_be_created_without_workspace(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('tasks.store'), [
            'title' => 'No Workspace Task',
            'status' => 'pending',
            'priority' => 'low',
            'due_date' => now()->addDay()->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('tasks.index'));
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('tasks', [
            'title' => 'No Workspace Task',
            'workspace_id' => null,
        ]);
    }

    public function test_task_cannot_be_assigned_to_another_users_workspace(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $workspace = Workspace::factory()->for($otherUser)->create();

        $response = $this->actingAs($user)->post(route('tasks.store'), [
            'title' => 'Sneaky Task',
            'status' => 'pending',
            'priority' => 'low',
            'due_date' => now()->addDay()->format('Y-m-d'),
            'workspace_id' => $workspace->id,
        ]);

        $response->assertSessionHasErrors('workspace_id');
    }

    public function test_task_workspace_can_be_updated(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->for($user)->create();
        $task = Task::factory()->for($user)->create();

        $response = $this->actingAs($user)->put(route('tasks.update', $task), [
            'title' => $task->title,
            'status' => $task->status,
            'priority' => $task->priority,
            'due_date' => $task->due_date->format('Y-m-d'),
            'workspace_id' => $workspace->id,
        ]);

        $response->assertRedirect(route('tasks.show', $task));
        $response->assertSessionHasNoErrors();

        $task->refresh();
        $this->assertEquals($workspace->id, $task->workspace_id);
    }

    public function test_task_workspace_can_be_removed(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->for($user)->create();
        $task = Task::factory()->for($user)->create(['workspace_id' => $workspace->id]);

        $response = $this->actingAs($user)->put(route('tasks.update', $task), [
            'title' => $task->title,
            'status' => $task->status,
            'priority' => $task->priority,
            'due_date' => $task->due_date->format('Y-m-d'),
            'workspace_id' => '',
        ]);

        $response->assertRedirect(route('tasks.show', $task));
        $response->assertSessionHasNoErrors();

        $task->refresh();
        $this->assertNull($task->workspace_id);
    }

    public function test_task_show_displays_workspace_name(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->for($user)->create(['name' => 'Engineering']);
        $task = Task::factory()->for($user)->create(['workspace_id' => $workspace->id]);

        $response = $this->actingAs($user)->get(route('tasks.show', $task));

        $response->assertOk();
        $response->assertSeeText('Engineering');
    }

    public function test_task_show_displays_none_when_no_workspace(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create(['workspace_id' => null]);

        $response = $this->actingAs($user)->get(route('tasks.show', $task));

        $response->assertOk();
        $response->assertSee('data-test="task-workspace"', false);
    }

    public function test_task_create_form_shows_workspace_select(): void
    {
        $user = User::factory()->create();
        Workspace::factory()->for($user)->create(['name' => 'Engineering']);

        $response = $this->actingAs($user)->get(route('tasks.create'));

        $response->assertOk();
        $response->assertSee('data-test="task-workspace-select"', false);
        $response->assertSeeText('Engineering');
    }

    public function test_task_edit_form_shows_workspace_select(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::factory()->for($user)->create(['name' => 'Marketing']);
        $task = Task::factory()->for($user)->create(['workspace_id' => $workspace->id]);

        $response = $this->actingAs($user)->get(route('tasks.edit', $task));

        $response->assertOk();
        $response->assertSee('data-test="task-workspace-select"', false);
        $response->assertSeeText('Marketing');
    }

    // ---------------------------------------------------------------
    // Search Filtering
    // ---------------------------------------------------------------

    public function test_index_search_filters_by_name(): void
    {
        $user = User::factory()->create();

        Workspace::factory()->for($user)->create(['name' => 'Engineering']);
        Workspace::factory()->for($user)->create(['name' => 'Marketing']);

        $response = $this->actingAs($user)->get(route('workspaces.index', ['search' => 'Engi']));

        $response->assertOk();
        $response->assertSeeText('Engineering');
        $response->assertDontSeeText('Marketing');
    }

    public function test_index_search_is_case_insensitive(): void
    {
        $user = User::factory()->create();

        Workspace::factory()->for($user)->create(['name' => 'Engineering']);

        $response = $this->actingAs($user)->get(route('workspaces.index', ['search' => 'engineering']));

        $response->assertOk();
        $response->assertSeeText('Engineering');
    }

    public function test_index_search_shows_no_matching_message(): void
    {
        $user = User::factory()->create();

        Workspace::factory()->for($user)->create(['name' => 'Engineering']);

        $response = $this->actingAs($user)->get(route('workspaces.index', ['search' => 'nonexistent']));

        $response->assertOk();
        $response->assertSeeText('No matching workspaces');
    }

    public function test_index_empty_search_is_ignored(): void
    {
        $user = User::factory()->create();

        Workspace::factory()->for($user)->create(['name' => 'Engineering']);
        Workspace::factory()->for($user)->create(['name' => 'Marketing']);

        $response = $this->actingAs($user)->get(route('workspaces.index', ['search' => '']));

        $response->assertOk();
        $response->assertSeeText('Engineering');
        $response->assertSeeText('Marketing');
    }

    public function test_index_search_does_not_leak_other_users_workspaces(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Workspace::factory()->for($user)->create(['name' => 'My Engineering']);
        Workspace::factory()->for($otherUser)->create(['name' => 'Other Engineering']);

        $response = $this->actingAs($user)->get(route('workspaces.index', ['search' => 'Engineering']));

        $response->assertOk();
        $response->assertSeeText('My Engineering');
        $response->assertDontSeeText('Other Engineering');
    }

    // ---------------------------------------------------------------
    // Has Tasks Filtering
    // ---------------------------------------------------------------

    public function test_index_filters_workspaces_with_tasks(): void
    {
        $user = User::factory()->create();

        $workspaceWithTasks = Workspace::factory()->for($user)->create(['name' => 'Busy']);
        Task::factory()->for($user)->create(['workspace_id' => $workspaceWithTasks->id]);

        Workspace::factory()->for($user)->create(['name' => 'Empty']);

        $response = $this->actingAs($user)->get(route('workspaces.index', ['has_tasks' => 'with_tasks']));

        $response->assertOk();
        $response->assertSeeText('Busy');
        $response->assertDontSeeText('Empty');
    }

    public function test_index_filters_workspaces_without_tasks(): void
    {
        $user = User::factory()->create();

        $workspaceWithTasks = Workspace::factory()->for($user)->create(['name' => 'Busy']);
        Task::factory()->for($user)->create(['workspace_id' => $workspaceWithTasks->id]);

        Workspace::factory()->for($user)->create(['name' => 'Empty']);

        $response = $this->actingAs($user)->get(route('workspaces.index', ['has_tasks' => 'without_tasks']));

        $response->assertOk();
        $response->assertSeeText('Empty');
        $response->assertDontSeeText('Busy');
    }

    public function test_index_invalid_has_tasks_filter_is_ignored(): void
    {
        $user = User::factory()->create();

        Workspace::factory()->for($user)->create(['name' => 'Engineering']);
        Workspace::factory()->for($user)->create(['name' => 'Marketing']);

        $response = $this->actingAs($user)->get(route('workspaces.index', ['has_tasks' => 'invalid']));

        $response->assertOk();
        $response->assertSeeText('Engineering');
        $response->assertSeeText('Marketing');
    }

    public function test_index_has_tasks_filter_dropdown_is_visible(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('workspaces.index'));

        $response->assertOk();
        $response->assertSee('data-test="filter-has-tasks"', false);
    }

    // ---------------------------------------------------------------
    // Extended Sorting
    // ---------------------------------------------------------------

    public function test_index_sort_newest_first(): void
    {
        $user = User::factory()->create();

        Workspace::factory()->for($user)->create(['name' => 'Old', 'created_at' => now()->subDays(5)]);
        Workspace::factory()->for($user)->create(['name' => 'New', 'created_at' => now()]);

        $response = $this->actingAs($user)->get(route('workspaces.index', ['sort' => 'newest']));

        $response->assertOk();
        $response->assertSeeTextInOrder(['New', 'Old']);
    }

    public function test_index_sort_oldest_first(): void
    {
        $user = User::factory()->create();

        Workspace::factory()->for($user)->create(['name' => 'Old', 'created_at' => now()->subDays(5)]);
        Workspace::factory()->for($user)->create(['name' => 'New', 'created_at' => now()]);

        $response = $this->actingAs($user)->get(route('workspaces.index', ['sort' => 'oldest']));

        $response->assertOk();
        $response->assertSeeTextInOrder(['Old', 'New']);
    }

    public function test_index_sort_most_tasks_first(): void
    {
        $user = User::factory()->create();

        $few = Workspace::factory()->for($user)->create(['name' => 'Few']);
        Task::factory()->for($user)->create(['workspace_id' => $few->id]);

        $many = Workspace::factory()->for($user)->create(['name' => 'Many']);
        Task::factory()->count(5)->for($user)->create(['workspace_id' => $many->id]);

        $response = $this->actingAs($user)->get(route('workspaces.index', ['sort' => 'tasks_desc']));

        $response->assertOk();
        $response->assertSeeTextInOrder(['Many', 'Few']);
    }

    public function test_index_sort_fewest_tasks_first(): void
    {
        $user = User::factory()->create();

        $few = Workspace::factory()->for($user)->create(['name' => 'Few']);
        Task::factory()->for($user)->create(['workspace_id' => $few->id]);

        $many = Workspace::factory()->for($user)->create(['name' => 'Many']);
        Task::factory()->count(5)->for($user)->create(['workspace_id' => $many->id]);

        $response = $this->actingAs($user)->get(route('workspaces.index', ['sort' => 'tasks_asc']));

        $response->assertOk();
        $response->assertSeeTextInOrder(['Few', 'Many']);
    }

    // ---------------------------------------------------------------
    // Combined Filters
    // ---------------------------------------------------------------

    public function test_index_search_and_has_tasks_combined(): void
    {
        $user = User::factory()->create();

        $match = Workspace::factory()->for($user)->create(['name' => 'Engineering Team']);
        Task::factory()->for($user)->create(['workspace_id' => $match->id]);

        Workspace::factory()->for($user)->create(['name' => 'Engineering Docs']);
        Workspace::factory()->for($user)->create(['name' => 'Marketing']);

        $response = $this->actingAs($user)->get(route('workspaces.index', [
            'search' => 'Engineering',
            'has_tasks' => 'with_tasks',
        ]));

        $response->assertOk();
        $response->assertSeeText('Engineering Team');
        $response->assertDontSeeText('Engineering Docs');
        $response->assertDontSeeText('Marketing');
    }

    public function test_index_filters_preserve_query_string(): void
    {
        $user = User::factory()->create();

        Workspace::factory()->count(20)->for($user)->sequence(
            fn ($sequence) => ['name' => 'Workspace '.str_pad((string) $sequence->index, 3, '0', STR_PAD_LEFT)],
        )->create();

        $response = $this->actingAs($user)->get(route('workspaces.index', [
            'search' => 'Workspace',
            'sort' => 'name_desc',
        ]));

        $response->assertOk();
        $response->assertSeeText('Showing');
    }

    // ---------------------------------------------------------------
    // Filter UI Elements
    // ---------------------------------------------------------------

    public function test_index_search_input_is_visible(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('workspaces.index'));

        $response->assertOk();
        $response->assertSee('data-test="search-input"', false);
    }

    public function test_index_filter_form_is_visible(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('workspaces.index'));

        $response->assertOk();
        $response->assertSee('data-test="workspace-filters"', false);
    }

    public function test_index_clear_filters_shown_when_filters_active(): void
    {
        $user = User::factory()->create();

        Workspace::factory()->for($user)->create();

        $response = $this->actingAs($user)->get(route('workspaces.index', ['search' => 'test']));

        $response->assertOk();
        $response->assertSee('data-test="clear-filters"', false);
    }

    public function test_index_clear_filters_hidden_when_no_filters(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('workspaces.index'));

        $response->assertOk();
        $response->assertDontSee('data-test="clear-filters"', false);
    }

    public function test_index_displays_total_count(): void
    {
        $user = User::factory()->create();

        Workspace::factory()->count(3)->for($user)->create();

        $response = $this->actingAs($user)->get(route('workspaces.index'));

        $response->assertOk();
        $response->assertSeeText('(3)');
    }
}
