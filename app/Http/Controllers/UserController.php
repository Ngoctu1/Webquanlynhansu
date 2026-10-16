<?php
namespace App\Http\Controllers;

use App\Http\Requests\{SendAccountInvitationRequest, UpdateUserStatusRequest, UpdateUserRoleRequest};

use App\Models\{Employee, Role, User};
use App\Services\{AccountInvitationService, AuditService};
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['employee', 'role'])->latest('id')->paginate(15);
        $roles = Role::whereIn('name', [Role::HR, Role::DIRECTOR, Role::EMPLOYEE, Role::ADMIN])->orderBy('id')->get();
        return view('admin.user.index', compact('users', 'roles'));
    }
    public function store(SendAccountInvitationRequest $request, Employee $employee, AccountInvitationService $service)
    {
        $sent = $service->send($request->user(), $employee);
        return redirect()->route('employees.show', $employee)->with($sent ? 'success' : 'warning',
            $sent ? 'Máy chủ gửi mail đã tiếp nhận lời mời. Nhân viên vui lòng kiểm tra hộp thư và thư rác.' : 'Tài khoản hoặc lời mời đã tồn tại. Không gửi thêm email.');
    }
    public function updateStatus(UpdateUserStatusRequest $request, User $user)
    {
        $actor = $request->user();
        abort_unless($actor->canManageHrm(), 403);
        $data = $request->validated();
        DB::transaction(function () use ($actor, $user, $data) {
            Role::where('name', Role::ADMIN)->lockForUpdate()->firstOrFail();
            $actor = $actor->fresh('role');
            abort_unless($actor->status === 'active' && $actor->canManageHrm(), 403);
            $user = User::lockForUpdate()->findOrFail($user->id);
            abort_unless($actor->hasRole(Role::ADMIN) || $user->hasRole(Role::EMPLOYEE), 403);
            if ($user->id === $actor->id && $data['status'] === 'locked') {
                throw ValidationException::withMessages(['status' => 'Không thể tự khóa tài khoản đang sử dụng.']);
            }
            if (!$user->activated_at || !$user->password || $user->status === 'pending_reset') {
                throw ValidationException::withMessages(['status' => 'Tài khoản chưa kích hoạt không thể mở khóa.']);
            }
            $old = $user->getAttributes();
            $user->forceFill([...$data, 'remember_token' => null])->save();
            AuditService::record($data['status'] === 'locked' ? 'USER_LOCKED' : 'USER_UNLOCKED', $user, $old);
        }, 3);
        return back()->with('success', 'Đã cập nhật trạng thái tài khoản.');
    }

    public function updateRole(UpdateUserRoleRequest $request, User $user)
    {
        abort_unless($request->user()->hasRole(Role::ADMIN), 403);
        $data = $request->validated();
        DB::transaction(function () use ($request, $user, $data) {
            // Serialize privilege changes and recheck the actor after a concurrent demotion.
            Role::where('name', Role::ADMIN)->lockForUpdate()->firstOrFail();
            $actor = $request->user()->fresh('role');
            abort_unless($actor->status === 'active' && $actor->hasRole(Role::ADMIN), 403);
            $user = User::lockForUpdate()->findOrFail($user->id);
            if ((int) $user->role_id === (int) $data['role_id']) { return; }
            if ($user->id === $request->user()->id) {
                throw ValidationException::withMessages(['role_id' => 'Không thể tự đổi vai trò của tài khoản đang sử dụng.']);
            }
            $old = $user->getAttributes();
            $user->forceFill(['role_id' => $data['role_id'], 'remember_token' => null])->save();
            AuditService::record('USER_ROLE_UPDATED', $user, $old);
        }, 3);
        return back()->with('success', 'Đã cập nhật vai trò tài khoản.');
    }
}
