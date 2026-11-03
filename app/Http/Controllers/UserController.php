<?php

namespace App\Http\Controllers;

use App\Http\Requests\{SendAccountInvitationRequest, UpdateUserRoleRequest, UpdateUserStatusRequest};
use App\Models\{Employee, Role, User};
use App\Services\{AccountInvitationService, AuditLogger};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Gate};
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $viewer = $request->user();

        $users = User::with(['employee', 'role'])
            ->when($viewer->isHR(), function ($q) {
                $q->whereDoesntHave('role', fn ($r) => $r->whereIn('name', [Role::ADMIN, Role::DIRECTOR]));
            })
            ->latest('id')
            ->paginate(15);

        $roles = Role::whereIn('name', [Role::ADMIN, Role::DIRECTOR, Role::HR, Role::EMPLOYEE])
            ->orderBy('id')
            ->get();

        return view('admin.user.index', compact('users', 'roles'));
    }

    public function store(SendAccountInvitationRequest $request, Employee $employee, AccountInvitationService $service)
    {
        $this->authorize('view', $employee);
        $sent = $service->send($request->user(), $employee);

        return redirect()
            ->route('employees.show', $employee)
            ->with(
                $sent ? 'success' : 'warning',
                $sent
                    ? 'Đã tạo liên kết kích hoạt / cấp lại mật khẩu và gửi tới email công ty (hiệu lực 48 giờ).'
                    : 'Không thể gửi email kích hoạt.'
            );
    }

    public function sendActivationMail(Request $request, User $user, AccountInvitationService $service)
    {
        $this->authorize('sendActivationOrResetMail', $user);

        $service->sendToUser($request->user(), $user);

        return back()->with('success', 'Đã gửi email kích hoạt / cấp lại mật khẩu cho ' . $user->email . '.');
    }

    public function updateStatus(UpdateUserStatusRequest $request, User $user)
    {
        $actor = $request->user();
        abort_unless($actor->hasManagementAccess(), 403);

        if ($actor->isHR() && ($user->isAdmin() || $user->isDirector())) {
            abort(403, 'HR không có quyền thao tác trên tài khoản Admin hoặc Director.');
        }

        $data = $request->validated();

        DB::transaction(function () use ($actor, $user, $data) {
            $user = User::lockForUpdate()->findOrFail($user->id);

            if ($user->id === $actor->id && $data['status'] === 'locked') {
                throw ValidationException::withMessages(['status' => 'Không thể tự khóa tài khoản đang sử dụng.']);
            }

            if (!$user->password && $data['status'] === 'active') {
                throw ValidationException::withMessages(['status' => 'Tài khoản chưa đặt mật khẩu không thể chuyển sang hoạt động trực tiếp.']);
            }

            $oldStatus = $user->status;
            $user->forceFill([
                'status' => $data['status'],
                'remember_token' => null,
            ])->save();

            AuditLogger::log(
                $data['status'] === 'locked' ? 'lock_user' : 'unlock_user',
                'user',
                $user->id,
                ['status' => $oldStatus],
                ['status' => $data['status']]
            );
        }, 3);

        return back()->with('success', 'Đã cập nhật trạng thái tài khoản.');
    }

    public function updateRole(UpdateUserRoleRequest $request, User $user)
    {
        Gate::authorize('manage-roles');
        $this->authorize('assignRole', $user);

        $data = $request->validated();

        DB::transaction(function () use ($request, $user, $data) {
            $user = User::lockForUpdate()->findOrFail($user->id);

            if ((int) $user->role_id === (int) $data['role_id']) {
                return;
            }

            if ($user->id === $request->user()->id) {
                throw ValidationException::withMessages(['role_id' => 'Không thể tự đổi vai trò của tài khoản đang sử dụng.']);
            }

            $oldValues = ['role_id' => $user->role_id, 'role_name' => $user->role?->name];
            $newRole = Role::findOrFail($data['role_id']);

            $user->forceFill([
                'role_id' => $newRole->id,
                'remember_token' => null,
            ])->save();

            AuditLogger::log(
                'update_role',
                'user',
                $user->id,
                $oldValues,
                ['role_id' => $newRole->id, 'role_name' => $newRole->name]
            );
        }, 3);

        return back()->with('success', 'Đã cập nhật vai trò tài khoản.');
    }
}
