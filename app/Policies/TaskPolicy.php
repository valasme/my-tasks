<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

/**
 * Authorization policy for {@see Task} resources.
 *
 * Ensures that users can only view, update, and delete their own tasks
 * while allowing any authenticated user to list and create tasks.
 */
class TaskPolicy
{
    /**
     * Determine whether the user can view any tasks.
     *
     * All authenticated users are allowed to view the task index.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the task.
     *
     * Only the task owner may view its details.
     */
    public function view(User $user, Task $task): bool
    {
        return $user->id === $task->user_id;
    }

    /**
     * Determine whether the user can create tasks.
     *
     * All authenticated users are allowed to create tasks.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the task.
     *
     * Only the task owner may update it.
     */
    public function update(User $user, Task $task): bool
    {
        return $user->id === $task->user_id;
    }

    /**
     * Determine whether the user can delete the task.
     *
     * Only the task owner may delete it.
     */
    public function delete(User $user, Task $task): bool
    {
        return $user->id === $task->user_id;
    }
}
