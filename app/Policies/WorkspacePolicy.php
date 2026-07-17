<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Workspace;

class WorkspacePolicy
{
    /**
     * Any member (including the owner) can view the workspace.
     */
    public function view(User $user, Workspace $workspace): bool
    {
        return $workspace->isMember($user->id);
    }

    /**
     * Only the owner can update workspace details.
     */
    public function update(User $user, Workspace $workspace): bool
    {
        return $workspace->owner_id === $user->id;
    }

    /**
     * Only the owner can delete the workspace.
     */
    public function delete(User $user, Workspace $workspace): bool
    {
        return $workspace->owner_id === $user->id;
    }

    /**
     * Only the owner can add/remove members.
     */
    public function manageMembers(User $user, Workspace $workspace): bool
    {
        return $workspace->owner_id === $user->id;
    }
}
