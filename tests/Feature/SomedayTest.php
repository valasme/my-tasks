<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SomedayTest extends TestCase
{
    use RefreshDatabase;

    // ---------------------------------------------------------------
    // Authentication Guards
    // ---------------------------------------------------------------

    public function test_guest_cannot_access_someday_index(): void
    {
        $response = $this->get(route('someday.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_access_someday_create(): void
    {
        $response = $this->get(route('someday.create'));

        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_store_someday_item(): void
    {
        $response = $this->post(route('someday.store'), [
            'title' => 'Learn guitar',
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_activate_someday_item(): void
    {
        $task = Task::factory()->create(['category' => 'someday_maybe']);

        $response = $this->post(route('someday.activate', $task));

        $response->assertRedirect(route('login'));
    }

    // ---------------------------------------------------------------
    // Authorization — Users cannot modify other users' items
    // ---------------------------------------------------------------

    public function test_user_cannot_activate_another_users_someday_item(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $task = Task::factory()->for($otherUser)->create([
            'category' => 'someday_maybe',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->post(route('someday.activate', $task));

        $response->assertForbidden();
    }

    // ---------------------------------------------------------------
    // Index
    // ---------------------------------------------------------------

    public function test_authenticated_user_can_view_someday_index(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('someday.index'));

        $response->assertOk();
        $response->assertSeeText('Someday');
    }

    public function test_index_shows_own_someday_items(): void
    {
        $user = User::factory()->create();
        Task::factory()->for($user)->create([
            'title' => 'Learn to paint',
            'category' => 'someday_maybe',
            'status' => 'pending',
            'priority' => 'low',
        ]);

        $response = $this->actingAs($user)->get(route('someday.index'));

        $response->assertOk();
        $response->assertSeeText('Learn to paint');
    }

    public function test_index_only_shows_own_items(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Task::factory()->for($user)->create([
            'title' => 'My someday',
            'category' => 'someday_maybe',
            'status' => 'pending',
            'priority' => 'low',
        ]);
        Task::factory()->for($otherUser)->create([
            'title' => 'Other someday',
            'category' => 'someday_maybe',
            'status' => 'pending',
            'priority' => 'low',
        ]);

        $response = $this->actingAs($user)->get(route('someday.index'));

        $response->assertOk();
        $response->assertSeeText('My someday');
        $response->assertDontSeeText('Other someday');
    }

    public function test_index_excludes_completed_someday_items(): void
    {
        $user = User::factory()->create();

        Task::factory()->for($user)->create([
            'title' => 'Active Someday',
            'category' => 'someday_maybe',
            'status' => 'pending',
            'priority' => 'low',
        ]);
        Task::factory()->completed()->for($user)->create([
            'title' => 'Completed Someday',
            'category' => 'someday_maybe',
        ]);

        $response = $this->actingAs($user)->get(route('someday.index'));

        $response->assertOk();
        $response->assertSeeText('Active Someday');
        $response->assertDontSeeText('Completed Someday');
    }

    public function test_index_paginates_someday_items(): void
    {
        $user = User::factory()->create();
        Task::factory()->count(20)->for($user)->create([
            'category' => 'someday_maybe',
            'status' => 'pending',
            'priority' => 'low',
        ]);

        $response = $this->actingAs($user)->get(route('someday.index'));

        $response->assertOk();
    }

    // ---------------------------------------------------------------
    // Create
    // ---------------------------------------------------------------

    public function test_user_can_view_someday_create_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('someday.create'));

        $response->assertOk();
    }

    // ---------------------------------------------------------------
    // Store
    // ---------------------------------------------------------------

    public function test_user_can_create_someday_item(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('someday.store'), [
            'title' => 'Write a novel',
            'description' => 'A thriller about AI.',
        ]);

        $response->assertRedirect(route('someday.index'));
        $response->assertSessionHas('success', 'Someday item created.');

        $this->assertDatabaseHas('tasks', [
            'user_id' => $user->id,
            'title' => 'Write a novel',
            'description' => 'A thriller about AI.',
            'category' => 'someday_maybe',
            'status' => 'pending',
            'priority' => 'low',
        ]);
    }

    public function test_someday_item_assigned_to_authenticated_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('someday.store'), [
            'title' => 'Travel the world',
        ]);

        $this->assertDatabaseHas('tasks', [
            'user_id' => $user->id,
            'category' => 'someday_maybe',
        ]);
    }

    public function test_store_requires_title(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('someday.store'), []);

        $response->assertSessionHasErrors('title');
    }

    public function test_store_rejects_title_exceeding_255_chars(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('someday.store'), [
            'title' => str_repeat('a', 256),
        ]);

        $response->assertSessionHasErrors('title');
    }

    public function test_store_description_is_optional(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('someday.store'), [
            'title' => 'No description',
        ]);

        $response->assertRedirect(route('someday.index'));
        $response->assertSessionHasNoErrors();
    }

    public function test_store_rejects_description_exceeding_5000_chars(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('someday.store'), [
            'title' => 'Long description',
            'description' => str_repeat('a', 5001),
        ]);

        $response->assertSessionHasErrors('description');
    }

    // ---------------------------------------------------------------
    // Activate
    // ---------------------------------------------------------------

    public function test_user_can_activate_someday_item(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create([
            'title' => 'Start podcast',
            'description' => 'Technology topics',
            'priority' => 'medium',
            'category' => 'someday_maybe',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->post(route('someday.activate', $task));

        $response->assertRedirect();
        $response->assertRedirectContains(route('tasks.create'));
    }

    public function test_activate_redirects_with_title_prefilled(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create([
            'title' => 'Start podcast',
            'category' => 'someday_maybe',
            'status' => 'pending',
            'priority' => 'low',
        ]);

        $response = $this->actingAs($user)->post(route('someday.activate', $task));

        $response->assertRedirectContains('title=Start%20podcast');
    }

    public function test_activate_redirects_with_from_someday_param(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create([
            'category' => 'someday_maybe',
            'status' => 'pending',
            'priority' => 'low',
        ]);

        $response = $this->actingAs($user)->post(route('someday.activate', $task));

        $response->assertRedirectContains('from_someday='.$task->id);
    }
}
