<?php

namespace App\Policies;

use App\Models\PomodoroSession;
use App\Models\User;

/**
 * Authorization policy for {@see PomodoroSession} resources.
 */
class PomodoroSessionPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, PomodoroSession $session): bool
    {
        return $user->id === $session->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, PomodoroSession $session): bool
    {
        return $user->id === $session->user_id;
    }

    public function delete(User $user, PomodoroSession $session): bool
    {
        return $user->id === $session->user_id;
    }
}
