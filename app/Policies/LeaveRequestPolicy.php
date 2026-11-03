<?php

namespace App\Policies;

use App\Models\{LeaveRequest, User};

class LeaveRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasManagementAccess();
    }

    public function view(User $user, LeaveRequest $leave): bool
    {
        if ($user->isAdmin() || $user->isDirector()) {
            return true;
        }

        if ($user->isHR()) {
            return !$leave->employee?->isAdminOrDirector();
        }

        if ($user->isEmployee()) {
            return $user->employee_id !== null && (int) $user->employee_id === (int) $leave->employee_id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        if ($user->isAdmin() || $user->employee_id === null) {
            return false;
        }

        return $user->isEmployee() || $user->isHR() || $user->isDirector();
    }

    public function cancel(User $user, LeaveRequest $leave): bool
    {
        return $user->employee_id !== null
            && (int) $user->employee_id === (int) $leave->employee_id
            && $leave->status === 'pending';
    }

    public function createRetroactive(User $user): bool
    {
        return $user->isHR() || $user->isDirector();
    }

    public function approveOrReject(User $user, LeaveRequest $leave): bool
    {
        if ($user->isEmployee() || $user->isAdmin()) {
            return false;
        }

        // Quy tắc 1: Không tự duyệt đơn của chính mình hoặc đơn do chính mình tạo
        if ($user->employee_id !== null && (int) $user->employee_id === (int) $leave->employee_id) {
            return false;
        }

        if ($leave->created_by !== null && (int) $user->id === (int) $leave->created_by) {
            return false;
        }

        // Quy tắc 2: Đơn của HR hoặc Đơn Nghỉ bù do HR nhập -> CHỈ Director được duyệt
        $isApplicantHr = (bool) $leave->employee?->user?->isHR();
        $isHrRetroactive = $leave->is_retroactive && (bool) $leave->creator?->isHR();

        if ($isApplicantHr || $isHrRetroactive) {
            return $user->isDirector();
        }

        // Quy tắc 3: Đơn của Employee thường -> HR hoặc Director đều duyệt được
        return $user->isHR() || $user->isDirector();
    }

    public function canApplyDirectly(User $user): bool
    {
        return $user->isDirector();
    }

    public function requiresApproval(User $user): bool
    {
        return $user->isHR() || $user->isEmployee();
    }
}
