<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasManagementAccess();
    }

    public function view(User $user, User $targetUser): bool
    {
        if ($user->isAdmin() || $user->isDirector()) {
            return true;
        }

        if ($user->isHR()) {
            return !$targetUser->isAdmin() && !$targetUser->isDirector();
        }

        return $user->id === $targetUser->id;
    }

    public function assignRole(User $user, ?User $targetUser = null): bool
    {
        return $user->isAdmin();
    }

    public function sendActivationOrResetMail(User $user, User $targetUser): bool
    {
        if ($user->isEmployee()) {
            return false;
        }

        if ($user->isHR()) {
            if ($targetUser->isAdmin() || $targetUser->isDirector()) {
                return false;
            }

            if (!$targetUser->isEmployee() && !$targetUser->isHR()) {
                return false;
            }
        }

        // Nếu là kích hoạt nhân viên mới (pending_reset và có hồ sơ nhân viên) thì bắt buộc phải có hợp đồng đã approved
        if ($targetUser->status === 'pending_reset' && $targetUser->employee_id !== null) {
            return $targetUser->employee()
                ->whereHas('contracts', fn ($q) => $q->where('approval_status', 'approved'))
                ->exists();
        }

        return $user->isAdmin() || $user->isDirector() || $user->isHR();
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
