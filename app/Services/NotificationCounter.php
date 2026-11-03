<?php

namespace App\Services;

use App\Models\{ChangeRequest, Contract, Department, Employee, LeaveRequest, Position, User};

class NotificationCounter
{
    /**
     * Tính toán trực tiếp bộ đếm công việc / thông báo theo đúng vai trò của người dùng.
     */
    public function getCountsFor(?User $user): array
    {
        if (!$user) {
            return [
                'role' => null,
                'total_badges' => 0,
                'items' => [],
            ];
        }

        if ($user->isHR()) {
            return $this->getHrCounts($user);
        }

        if ($user->isDirector()) {
            return $this->getDirectorCounts($user);
        }

        if ($user->isEmployee()) {
            return $this->getEmployeeCounts($user);
        }

        return [
            'role' => 'Admin',
            'total_badges' => 0,
            'items' => [],
        ];
    }

    /**
     * Bộ đếm cho HR (Bắt buộc lọc qua Employee::visibleTo($user), không bao giờ đếm Admin/Director).
     */
    public function getHrCounts(User $user): array
    {
        // Đơn nghỉ phép đang pending của Employee thường (HR được quyền duyệt)
        $pendingLeavesQuery = LeaveRequest::where('status', 'pending')
            ->where('is_retroactive', false)
            ->whereHas('employee', function ($q) use ($user) {
                $q->visibleTo($user)
                    ->where('id', '!=', (int) $user->employee_id)
                    ->whereDoesntHave('user.role', fn ($rq) => $rq->whereIn('name', ['Admin', 'Director', 'HR']));
            });

        $pendingLeaves = (clone $pendingLeavesQuery)->count();
        $stalePendingLeaves = (clone $pendingLeavesQuery)
            ->where('created_at', '<=', now()->subDays(2))
            ->count();

        // Hợp đồng sắp hết hạn trong vòng 1 đến 2 tháng tới (lọc qua visibleTo)
        $expiringContracts = Contract::where('status', 'active')
            ->whereNotNull('effective_to')
            ->whereBetween('effective_to', [now()->toDateString(), now()->addMonths(2)->toDateString()])
            ->whereHas('employee', fn ($q) => $q->visibleTo($user))
            ->count();

        // Hợp đồng hoặc hồ sơ bị Director từ chối (rejected) cần HR sửa lại
        $rejectedContracts = Contract::where('approval_status', 'rejected')
            ->whereHas('employee', fn ($q) => $q->visibleTo($user))
            ->count();
        $rejectedEmployees = Employee::visibleTo($user)
            ->where('approval_status', 'rejected')
            ->count();
        $rejectedItems = $rejectedContracts + $rejectedEmployees;

        $totalBadges = $pendingLeaves + $expiringContracts + $rejectedItems;

        return [
            'role' => 'HR',
            'pending_leaves' => $pendingLeaves,
            'stale_pending_leaves' => $stalePendingLeaves,
            'expiring_contracts' => $expiringContracts,
            'rejected_contracts' => $rejectedContracts,
            'rejected_employees' => $rejectedEmployees,
            'rejected_items' => $rejectedItems,
            'total_badges' => $totalBadges,
            'items' => [
                [
                    'label' => 'Đơn nghỉ phép chờ duyệt',
                    'count' => $pendingLeaves,
                    'subtext' => $stalePendingLeaves > 0 ? "{$stalePendingLeaves} đơn quá 2 ngày chưa duyệt!" : 'Đơn của nhân viên',
                    'urgent' => $stalePendingLeaves > 0,
                    'url' => route('leave-requests.index', ['status' => 'pending', 'week' => 'all']),
                ],
                [
                    'label' => 'Hợp đồng sắp hết hạn (1-2 tháng)',
                    'count' => $expiringContracts,
                    'subtext' => 'Cần chuẩn bị gia hạn',
                    'urgent' => false,
                    'url' => route('contracts.index', ['expiring' => '2']),
                ],
                [
                    'label' => 'Hợp đồng / Hồ sơ bị từ chối',
                    'count' => $rejectedItems,
                    'subtext' => "{$rejectedContracts} hợp đồng, {$rejectedEmployees} hồ sơ cần sửa",
                    'urgent' => $rejectedItems > 0,
                    'url' => route('contracts.index', ['status' => 'rejected']),
                ],
            ],
        ];
    }

    /**
     * Bộ đếm cho Director.
     */
    public function getDirectorCounts(User $user): array
    {
        $pendingEmployees = Employee::where('approval_status', 'pending_approval')->count();
        $pendingContracts = Contract::where('approval_status', 'pending_approval')->count();
        $pendingDepartments = Department::where('approval_status', 'pending_approval')->count();
        $pendingPositions = Position::where('approval_status', 'pending_approval')->count();
        $pendingChangeRequests = ChangeRequest::where('status', 'pending')->count();
        $pendingRetroactiveLeaves = LeaveRequest::where('status', 'pending')
            ->where('is_retroactive', true)
            ->count();

        $pendingHrProposals = $pendingEmployees
            + $pendingContracts
            + $pendingDepartments
            + $pendingPositions
            + $pendingChangeRequests
            + $pendingRetroactiveLeaves;

        // Tổng đơn nghỉ phép đang pending (gồm cả đơn của HR và Employee)
        $pendingLeaves = LeaveRequest::where('status', 'pending')->count();
        $stalePendingLeaves = LeaveRequest::where('status', 'pending')
            ->where('created_at', '<=', now()->subDays(2))
            ->count();

        // Hợp đồng sắp hết hạn trong 1 đến 2 tháng tới
        $expiringContracts = Contract::where('status', 'active')
            ->whereNotNull('effective_to')
            ->whereBetween('effective_to', [now()->toDateString(), now()->addMonths(2)->toDateString()])
            ->count();

        $totalBadges = $pendingHrProposals + $pendingLeaves + $expiringContracts;

        return [
            'role' => 'Director',
            'pending_hr_proposals' => $pendingHrProposals,
            'pending_employees' => $pendingEmployees,
            'pending_contracts' => $pendingContracts,
            'pending_departments' => $pendingDepartments,
            'pending_positions' => $pendingPositions,
            'pending_change_requests' => $pendingChangeRequests,
            'pending_retroactive_leaves' => $pendingRetroactiveLeaves,
            'pending_leaves' => $pendingLeaves,
            'stale_pending_leaves' => $stalePendingLeaves,
            'expiring_contracts' => $expiringContracts,
            'total_badges' => $totalBadges,
            'items' => [
                [
                    'label' => 'Đề xuất của HR chờ duyệt',
                    'count' => $pendingHrProposals,
                    'subtext' => "Hồ sơ/HĐ/Danh mục mới ({$pendingContracts} HĐ, {$pendingEmployees} NV) & {$pendingChangeRequests} sửa/xóa",
                    'urgent' => $pendingHrProposals > 0,
                    'url' => route('change-requests.index'),
                ],
                [
                    'label' => 'Đơn nghỉ phép chờ duyệt',
                    'count' => $pendingLeaves,
                    'subtext' => $stalePendingLeaves > 0 ? "{$stalePendingLeaves} đơn quá 2 ngày chưa xử lý!" : 'Đơn của HR & Nhân viên',
                    'urgent' => $stalePendingLeaves > 0,
                    'url' => route('leave-requests.index', ['status' => 'pending', 'week' => 'all']),
                ],
                [
                    'label' => 'Hợp đồng sắp hết hạn (1-2 tháng)',
                    'count' => $expiringContracts,
                    'subtext' => 'Trong 60 ngày tới',
                    'urgent' => false,
                    'url' => route('contracts.index', ['expiring' => '2']),
                ],
            ],
        ];
    }

    /**
     * Bộ đếm cho Employee (Đơn nghỉ phép của chính mình vừa được approved, rejected, hoặc expired gần đây).
     */
    public function getEmployeeCounts(User $user): array
    {
        $employeeId = (int) $user->employee_id;
        if (!$employeeId) {
            return [
                'role' => 'Employee',
                'total_badges' => 0,
                'items' => [],
            ];
        }

        $recentSince = now()->subDays(30);

        $approvedCount = LeaveRequest::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->where('updated_at', '>=', $recentSince)
            ->count();

        $rejectedCount = LeaveRequest::where('employee_id', $employeeId)
            ->where('status', 'rejected')
            ->where('updated_at', '>=', $recentSince)
            ->count();

        $expiredCount = LeaveRequest::where('employee_id', $employeeId)
            ->where('status', 'expired')
            ->where('updated_at', '>=', $recentSince)
            ->count();

        $totalBadges = $approvedCount + $rejectedCount + $expiredCount;

        return [
            'role' => 'Employee',
            'approved_leaves' => $approvedCount,
            'rejected_leaves' => $rejectedCount,
            'expired_leaves' => $expiredCount,
            'total_badges' => $totalBadges,
            'items' => [
                [
                    'label' => 'Đơn nghỉ phép đã được duyệt',
                    'count' => $approvedCount,
                    'subtext' => 'Trong 30 ngày gần đây',
                    'urgent' => false,
                    'url' => route('user.leave-requests.index'),
                ],
                [
                    'label' => 'Đơn nghỉ phép bị từ chối',
                    'count' => $rejectedCount,
                    'subtext' => 'Xem lý do từ chối',
                    'urgent' => $rejectedCount > 0,
                    'url' => route('user.leave-requests.index'),
                ],
                [
                    'label' => 'Đơn nghỉ phép quá hạn (expired)',
                    'count' => $expiredCount,
                    'subtext' => 'Hết hạn do chưa xử lý kịp',
                    'urgent' => $expiredCount > 0,
                    'url' => route('user.leave-requests.index'),
                ],
            ],
        ];
    }
}
