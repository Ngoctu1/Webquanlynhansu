<?php

namespace App\Policies;

use App\Models\{Employee, User};

class EmployeePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasManagementAccess();
    }

    public function view(User $user, Employee $employee): bool
    {
        if ($user->isAdmin() || $user->isDirector()) {
            return true;
        }

        if ($user->isHR()) {
            return !$employee->isAdminOrDirector();
        }

        if ($user->isEmployee()) {
            return $user->employee_id !== null && $user->employee_id === $employee->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasManagementAccess();
    }

    public function update(User $user, Employee $employee): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isDirector()) {
            return $user->employee_id !== $employee->id;
        }

        if ($user->isHR()) {
            return !$employee->isAdminOrDirector();
        }

        return false;
    }

    public function delete(User $user, Employee $employee): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isDirector()) {
            return $user->employee_id !== $employee->id;
        }

        return false;
    }

    public function canApplyDirectly(User $user, ?Employee $employee = null): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isDirector()) {
            return $employee === null || $user->employee_id !== $employee->id;
        }

        return false;
    }

    public function requiresApproval(User $user, ?Employee $employee = null): bool
    {
        if (!$user->isHR()) {
            return false;
        }

        return $employee === null || !$employee->isAdminOrDirector();
    }
}
