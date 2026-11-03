<?php

namespace App\Policies;

use App\Models\{EmployeeDocument, User};

class EmployeeDocumentPolicy
{
    public function view(User $user, EmployeeDocument $document): bool
    {
        return $this->download($user, $document);
    }

    public function download(User $user, EmployeeDocument $document): bool
    {
        if ($user->isAdmin() || $user->isDirector()) {
            return true;
        }

        if ($user->isHR()) {
            return !$document->employee?->isAdminOrDirector();
        }

        if ($user->isEmployee()) {
            return $user->employee_id !== null && $user->employee_id === $document->employee_id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasManagementAccess();
    }

    public function delete(User $user, EmployeeDocument $document): bool
    {
        if ($user->isAdmin() || $user->isDirector()) {
            return true;
        }

        if ($user->isHR()) {
            return !$document->employee?->isAdminOrDirector();
        }

        return false;
    }
}
