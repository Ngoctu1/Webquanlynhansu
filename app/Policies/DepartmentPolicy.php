<?php

namespace App\Policies;

use App\Models\{Department, User};

class DepartmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasManagementAccess();
    }

    public function view(User $user, Department $department): bool
    {
        return $user->hasManagementAccess();
    }

    public function create(User $user): bool
    {
        return $user->hasManagementAccess();
    }

    public function update(User $user, Department $department): bool
    {
        return $user->hasManagementAccess();
    }

    public function delete(User $user, Department $department): bool
    {
        if (!$user->hasManagementAccess()) {
            return false;
        }

        if ($department->employees()->count() > 0) {
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
