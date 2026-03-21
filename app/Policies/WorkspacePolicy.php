<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Workspace;

/**
 * Authorization policy for {@see Workspace} resources.
 *
 * Ensures that users can only view, update, and delete their own workspaces
 * while allowing any authenticated user to list and create workspaces.
 */
class WorkspacePolicy
{
    /**
     * Determine whether the user can view any workspaces.
     *
     * All authenticated users are allowed to view the workspace index.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the workspace.
     *
     * Only the workspace owner may view its details.
     */
    public function view(User $user, Workspace $workspace): bool
    {
        return $user->id === $workspace->user_id;
    }

    /**
     * Determine whether the user can create workspaces.
     *
     * All authenticated users are allowed to create workspaces.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the workspace.
     *
     * Only the workspace owner may update it.
     */
    public function update(User $user, Workspace $workspace): bool
    {
        return $user->id === $workspace->user_id;
    }

    /**
     * Determine whether the user can delete the workspace.
     *
     * Only the workspace owner may delete it.
     */
    public function delete(User $user, Workspace $workspace): bool
    {
        return $user->id === $workspace->user_id;
    }
}
