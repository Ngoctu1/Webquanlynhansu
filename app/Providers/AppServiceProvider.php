<?php

namespace App\Providers;

use App\Models\{
    ChangeRequest,
    Contract,
    Department,
    Employee,
    EmployeeDocument,
    LeaveRequest,
    Position,
    User
};
use App\Policies\{
    ChangeRequestPolicy,
    ContractPolicy,
    DepartmentPolicy,
    EmployeeDocumentPolicy,
    EmployeePolicy,
    LeaveRequestPolicy,
    PositionPolicy,
    UserPolicy
};
use App\Services\NotificationCounter;
use Illuminate\Support\Facades\{Gate, View};
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Department::class, DepartmentPolicy::class);
        Gate::policy(Position::class, PositionPolicy::class);
        Gate::policy(Employee::class, EmployeePolicy::class);
        Gate::policy(Contract::class, ContractPolicy::class);
        Gate::policy(LeaveRequest::class, LeaveRequestPolicy::class);
        Gate::policy(EmployeeDocument::class, EmployeeDocumentPolicy::class);
        Gate::policy(ChangeRequest::class, ChangeRequestPolicy::class);

        // Gates bổ sung cho Nhật ký hệ thống (Audit Logs) và Duyệt đề xuất
        Gate::define('view-audit-logs', fn (User $user): bool => $user->isAdmin() || $user->isDirector());
        Gate::define('review-change-requests', fn (User $user): bool => $user->isDirector());
        Gate::define('assign-role', fn (User $user): bool => $user->isAdmin());
        Gate::define('manage-roles', fn (User $user): bool => $user->isAdmin());
        Gate::define('access-management', fn (User $user): bool => $user->hasManagementAccess());

        // View Composer đếm trực tiếp công việc cần xử lý / thông báo theo vai trò
        View::composer(['admin.layout.masterlayout', 'user.layout.masterlayout', 'admin.welcome'], function ($view) {
            $counter = app(NotificationCounter::class);
            $view->with('notificationCounts', $counter->getCountsFor(auth()->user()));
        });
    }
}
