<?php

namespace App\Policies;

use App\Models\MoodLog;
use App\Models\User;

/**
 * Authorization policy for {@see MoodLog} resources.
 */
class MoodLogPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, MoodLog $log): bool
    {
        return $user->id === $log->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function delete(User $user, MoodLog $log): bool
    {
        return $user->id === $log->user_id;
    }
}
