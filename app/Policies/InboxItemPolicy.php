<?php

namespace App\Policies;

use App\Models\InboxItem;
use App\Models\User;

/**
 * Authorization policy for {@see InboxItem} resources.
 */
class InboxItemPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, InboxItem $item): bool
    {
        return $user->id === $item->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, InboxItem $item): bool
    {
        return $user->id === $item->user_id;
    }

    public function delete(User $user, InboxItem $item): bool
    {
        return $user->id === $item->user_id;
    }
}
