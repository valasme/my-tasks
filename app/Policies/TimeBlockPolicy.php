<?php

namespace App\Policies;

use App\Models\TimeBlock;
use App\Models\User;

/**
 * Authorization policy for {@see TimeBlock} resources.
 */
class TimeBlockPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, TimeBlock $timeBlock): bool
    {
        return $user->id === $timeBlock->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, TimeBlock $timeBlock): bool
    {
        return $user->id === $timeBlock->user_id;
    }

    public function delete(User $user, TimeBlock $timeBlock): bool
    {
        return $user->id === $timeBlock->user_id;
    }
}
