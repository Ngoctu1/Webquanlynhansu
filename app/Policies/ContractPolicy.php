<?php

namespace App\Policies;

use App\Models\{Contract, User};

class ContractPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasManagementAccess();
    }

    public function view(User $user, Contract $contract): bool
    {
        if ($user->isAdmin() || $user->isDirector()) {
            return true;
        }

        if ($user->isHR()) {
            return !$contract->employee?->isAdminOrDirector();
        }

        if ($user->isEmployee()) {
            return $user->employee_id !== null && (int) $user->employee_id === (int) $contract->employee_id;
        }

        return false;
    }

    public function download(User $user, Contract $contract): bool
    {
        return $this->view($user, $contract);
    }

    public function create(User $user): bool
    {
        return $user->isHR() || $user->isDirector();
    }

    public function update(User $user, Contract $contract): bool
    {
        if (in_array($contract->status, ['expired', 'terminated'], true)) {
            return $this->updateOld($user, $contract);
        }

        if ($contract->status === 'active' || in_array($contract->approval_status, ['rejected', 'pending_approval', 'draft'], true)) {
            return $this->updateActive($user, $contract);
        }

        return false;
    }

    public function updateActive(User $user, Contract $contract): bool
    {
        if (in_array($contract->status, ['expired', 'terminated'], true)) {
            return false;
        }

        if ($user->isDirector()) {
            return (int) $user->employee_id !== (int) $contract->employee_id;
        }

        if ($user->isHR()) {
            return !$contract->employee?->isAdminOrDirector();
        }

        return false;
    }

    public function updateOld(User $user, Contract $contract): bool
    {
        if (!in_array($contract->status, ['expired', 'terminated'], true)) {
            return false;
        }

        return $user->isDirector() && (int) $user->employee_id !== (int) $contract->employee_id;
    }

    public function approve(User $user, Contract $contract): bool
    {
        return $user->isDirector() && (int) $user->employee_id !== (int) $contract->employee_id;
    }

    public function canApplyDirectly(User $user, ?Contract $contract = null): bool
    {
        if (!$user->isDirector()) {
            return false;
        }

        return $contract === null || (int) $user->employee_id !== (int) $contract->employee_id;
    }

    public function requiresApproval(User $user, ?Contract $contract = null): bool
    {
        if (!$user->isHR()) {
            return false;
        }

        if ($contract === null) {
            return true;
        }

        return $contract->status === 'active'
            && $contract->approval_status === 'approved'
            && !$contract->employee?->isAdminOrDirector();
    }
}
