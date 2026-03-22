<?php

namespace Tests\Feature;

use App\Models\InboxItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InboxTest extends TestCase
{
    use RefreshDatabase;

    // ---------------------------------------------------------------
    // Authentication Guards
    // ---------------------------------------------------------------

    public function test_guest_cannot_access_inbox_index(): void
    {
        $response = $this->get(route('inbox.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_store_inbox_item(): void
    {
        $response = $this->post(route('inbox.store'), [
            'body' => 'Some thought',
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_convert_inbox_item(): void
    {
        $item = InboxItem::factory()->create();

        $response = $this->post(route('inbox.convert', $item));

        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_delete_inbox_item(): void
    {
        $item = InboxItem::factory()->create();

        $response = $this->delete(route('inbox.destroy', $item));

        $response->assertRedirect(route('login'));
    }

    // ---------------------------------------------------------------
    // Authorization — Users cannot access other users' inbox items
    // ---------------------------------------------------------------

    public function test_user_cannot_convert_another_users_inbox_item(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $item = InboxItem::factory()->for($otherUser)->create();

        $response = $this->actingAs($user)->post(route('inbox.convert', $item));

        $response->assertForbidden();
    }

    public function test_user_cannot_delete_another_users_inbox_item(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $item = InboxItem::factory()->for($otherUser)->create();

        $response = $this->actingAs($user)->delete(route('inbox.destroy', $item));

        $response->assertForbidden();
    }

    // ---------------------------------------------------------------
    // Index
    // ---------------------------------------------------------------

    public function test_authenticated_user_can_view_inbox(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('inbox.index'));

        $response->assertOk();
        $response->assertSeeText('Inbox');
    }

    public function test_index_only_shows_unprocessed_items(): void
    {
        $user = User::factory()->create();

        $unprocessed = InboxItem::factory()->for($user)->create(['body' => 'Unprocessed thought']);
        $processed = InboxItem::factory()->for($user)->processed()->create(['body' => 'Processed thought']);

        $response = $this->actingAs($user)->get(route('inbox.index'));

        $response->assertOk();
        $response->assertSeeText('Unprocessed thought');
        $response->assertDontSeeText('Processed thought');
    }

    public function test_index_shows_processed_count(): void
    {
        $user = User::factory()->create();
        InboxItem::factory()->count(3)->for($user)->processed()->create();

        $response = $this->actingAs($user)->get(route('inbox.index'));

        $response->assertOk();
        $response->assertSeeText('3');
    }

    public function test_index_only_shows_own_unprocessed_items(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        InboxItem::factory()->for($user)->create(['body' => 'My thought']);
        InboxItem::factory()->for($otherUser)->create(['body' => 'Other thought']);

        $response = $this->actingAs($user)->get(route('inbox.index'));

        $response->assertOk();
        $response->assertSeeText('My thought');
        $response->assertDontSeeText('Other thought');
    }

    public function test_index_paginates_items(): void
    {
        $user = User::factory()->create();
        InboxItem::factory()->count(20)->for($user)->create();

        $response = $this->actingAs($user)->get(route('inbox.index'));

        $response->assertOk();
    }

    // ---------------------------------------------------------------
    // Store
    // ---------------------------------------------------------------

    public function test_user_can_create_inbox_item(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('inbox.store'), [
            'body' => 'New inbox thought',
        ]);

        $response->assertRedirect(route('inbox.index'));
        $response->assertSessionHas('success', 'Item captured.');

        $this->assertDatabaseHas('inbox_items', [
            'user_id' => $user->id,
            'body' => 'New inbox thought',
            'is_processed' => false,
        ]);
    }

    public function test_inbox_item_assigned_to_authenticated_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('inbox.store'), [
            'body' => 'Some thought',
        ]);

        $this->assertDatabaseHas('inbox_items', [
            'user_id' => $user->id,
        ]);
    }

    public function test_store_requires_body(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('inbox.store'), []);

        $response->assertSessionHasErrors('body');
    }

    public function test_store_rejects_empty_body(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('inbox.store'), [
            'body' => '',
        ]);

        $response->assertSessionHasErrors('body');
    }

    public function test_store_rejects_body_exceeding_2000_chars(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('inbox.store'), [
            'body' => str_repeat('a', 2001),
        ]);

        $response->assertSessionHasErrors('body');
    }

    public function test_store_accepts_body_at_max_2000_chars(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('inbox.store'), [
            'body' => str_repeat('a', 2000),
        ]);

        $response->assertRedirect(route('inbox.index'));
        $response->assertSessionHasNoErrors();
    }

    // ---------------------------------------------------------------
    // Convert to Task
    // ---------------------------------------------------------------

    public function test_user_can_convert_inbox_item_to_task(): void
    {
        $user = User::factory()->create();
        $item = InboxItem::factory()->for($user)->create(['body' => 'Research topic']);

        $response = $this->actingAs($user)->post(route('inbox.convert', $item));

        $response->assertRedirect();
        $response->assertRedirectContains(route('tasks.create'));

        $this->assertDatabaseHas('inbox_items', [
            'id' => $item->id,
            'is_processed' => true,
        ]);
    }

    public function test_convert_redirects_with_title_prefilled(): void
    {
        $user = User::factory()->create();
        $item = InboxItem::factory()->for($user)->create(['body' => 'Call dentist']);

        $response = $this->actingAs($user)->post(route('inbox.convert', $item));

        $response->assertRedirectContains('title=Call%20dentist');
    }

    public function test_convert_redirects_with_from_inbox_param(): void
    {
        $user = User::factory()->create();
        $item = InboxItem::factory()->for($user)->create();

        $response = $this->actingAs($user)->post(route('inbox.convert', $item));

        $response->assertRedirectContains('from_inbox='.$item->id);
    }

    // ---------------------------------------------------------------
    // Destroy
    // ---------------------------------------------------------------

    public function test_user_can_delete_inbox_item(): void
    {
        $user = User::factory()->create();
        $item = InboxItem::factory()->for($user)->create();

        $response = $this->actingAs($user)->delete(route('inbox.destroy', $item));

        $response->assertRedirect(route('inbox.index'));
        $response->assertSessionHas('success', 'Item deleted.');

        $this->assertDatabaseMissing('inbox_items', ['id' => $item->id]);
    }
}
