<?php

namespace App\Policies;

use App\Models\{Position, User};

class PositionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasManagementAccess();
    }

    public function view(User $user, Position $position): bool
    {
        return $user->hasManagementAccess();
    }

    public function create(User $user): bool
    {
        return $user->hasManagementAccess();
    }

    public function update(User $user, Position $position): bool
    {
        return $user->hasManagementAccess();
    }

    public function delete(User $user, Position $position): bool
    {
        if (!$user->hasManagementAccess()) {
            return false;
        }

        if ($position->employees()->count() > 0) {
            return false;
        }

        return true;
    }

    public function canApplyDirectly(User $user): bool
    {
        return $user->isAdmin() || $user->isDirector();
    }

    public function requiresApproval(User $user): bool
    {
        return $user->isHR();
    }
}
