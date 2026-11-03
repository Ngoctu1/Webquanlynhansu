<?php

namespace App\Http\Controllers;

use App\Http\Requests\{FilterEmployeesRequest, RejectApprovalRequest, SaveEmployeeRequest};
use App\Models\{ChangeRequest, Department, Employee, Position, Role, User};
use App\Services\{AuditLogger, RecordCodeService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Storage};
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmployeeController extends Controller
{
    public function index(FilterEmployeesRequest $request)
    {
        $this->authorize('viewAny', Employee::class);

        $viewer = $request->user();
        $filters = $request->validated();

        $employees = Employee::visibleTo($viewer)
            ->with(['department', 'position', 'user.role'])
            ->when(isset($filters['q']), function ($q) use ($filters) {
                $q->where(function ($sub) use ($filters) {
                    foreach (['employee_code', 'full_name', 'personal_email', 'work_email', 'phone', 'citizen_id'] as $field) {
                        $sub->orWhere($field, 'like', '%' . $filters['q'] . '%');
                    }
                });
            });

        foreach (['department_id', 'position_id', 'employment_status'] as $field) {
            if (!empty($filters[$field])) {
                $employees->where($field, $filters[$field]);
            }
        }

        $employees = $employees->orderByDesc('id')->paginate(15)->withQueryString();

        return view('admin.employee.index', [
            'employees' => $employees,
            'filters' => $filters,
            'departments' => Department::orderBy('name')->get(),
            'positions' => Position::orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        $this->authorize('create', Employee::class);

        return view('admin.employee.create', $this->options());
    }

    public function store(SaveEmployeeRequest $request, RecordCodeService $codes)
    {
        $this->authorize('create', Employee::class);

        $actor = $request->user();
        $data = $request->payload();
        $uploadedAvatar = $request->file('avatar') ?? $request->file('profile_photo');
        $avatarPath = $uploadedAvatar?->store('employee-profiles', 'local');

        $data['employment_status'] = 'pending';
        $data['annual_leave_days'] = $data['annual_leave_days'] ?? 12;

        if ($actor->needsApproval()) {
            $data['approval_status'] = 'pending_approval';
            $data['approved_by'] = null;
            $data['approved_at'] = null;
        } else {
            $data['approval_status'] = 'approved';
            $data['approved_by'] = $actor->id;
            $data['approved_at'] = now();
        }

        try {
            $createCallback = function (string $code) use ($data, $avatarPath) {
                $employee = Employee::create([
                    ...$data,
                    'employee_code' => $code,
                    'avatar' => $avatarPath,
                ]);

                $employeeRole = Role::where('name', Role::EMPLOYEE)->firstOrFail();

                $user = User::create([
                    'employee_id' => $employee->id,
                    'role_id' => $employeeRole->id,
                    'email' => $employee->work_email,
                    'password' => null,
                    'status' => 'pending_reset',
                ]);

                AuditLogger::log('create', 'employee', $employee->id, null, $employee->toArray());
                AuditLogger::log('create', 'user', $user->id, null, [
                    'employee_id' => $employee->id,
                    'email' => $user->email,
                    'role_id' => $user->role_id,
                    'status' => $user->status,
                ]);

                return $employee;
            };

            if (!empty($data['employee_code'])) {
                $employee = DB::transaction(fn () => $createCallback($data['employee_code']), 3);
            } else {
                $employee = $codes->employee($createCallback);
            }
        } catch (\Throwable $e) {
            if ($avatarPath) {
                Storage::disk('local')->delete($avatarPath);
            }
            throw $e;
        }

        $msg = $actor->needsApproval()
            ? 'Đã tạo hồ sơ nhân viên và tài khoản chờ kích hoạt (đang chờ Giám đốc duyệt hồ sơ).'
            : 'Đã tạo hồ sơ nhân viên và tài khoản chờ kích hoạt thành công.';

        return redirect()->route('employees.show', $employee)->with('success', $msg);
    }

    public function show(Request $request, Employee $employee)
    {
        $employee = Employee::visibleTo($request->user())
            ->with([
                'department',
                'position',
                'user.role',
                'employeeDocuments.uploader',
                'contracts.approver',
                'activeContract',
            ])
            ->findOrFail($employee->id);

        $this->authorize('view', $employee);

        $hasApprovedContract = $employee->contracts()->where('approval_status', 'approved')->exists();

        return view('admin.employee.show', compact('employee', 'hasApprovedContract'));
    }

    public function edit(Request $request, Employee $employee)
    {
        $employee = Employee::visibleTo($request->user())->findOrFail($employee->id);
        $this->authorize('update', $employee);

        return view('admin.employee.edit', ['employee' => $employee, ...$this->options($employee)]);
    }

    public function update(SaveEmployeeRequest $request, Employee $employee)
    {
        $actor = $request->user();
        $employee = Employee::visibleTo($actor)->findOrFail($employee->id);
        $this->authorize('update', $employee);

        $uploadedAvatar = $request->file('avatar') ?? $request->file('profile_photo');
        $newAvatarPath = $uploadedAvatar?->store('employee-profiles', 'local');
        $payload = $request->payload();
        unset($payload['employee_code']);

        if ($newAvatarPath) {
            $payload['avatar'] = $newAvatarPath;
        }

        if ($actor->needsApproval()) {
            DB::transaction(function () use ($actor, $employee, $payload) {
                $oldData = $employee->only(array_keys($payload));
                ChangeRequest::create([
                    'entity_type' => 'employee',
                    'entity_id' => $employee->id,
                    'action' => 'update',
                    'old_data' => $oldData,
                    'new_data' => $payload,
                    'status' => 'pending',
                    'requested_by' => $actor->id,
                    'requested_at' => now(),
                ]);

                AuditLogger::log('request_update', 'employee', $employee->id, $oldData, $payload);
            });

            return redirect()
                ->route('employees.show', $employee)
                ->with('success', 'Đề xuất chỉnh sửa hồ sơ nhân viên đã được gửi chờ Giám đốc phê duyệt.');
        }

        $oldAvatarPath = null;
        try {
            DB::transaction(function () use ($employee, $payload, &$oldAvatarPath) {
                $employee = Employee::lockForUpdate()->findOrFail($employee->id);
                $old = $employee->only(array_keys($payload));
                $oldAvatarPath = $employee->avatar;
                $oldWorkEmail = $employee->work_email;

                $employee->fill($payload);
                if ($employee->isDirty()) {
                    $employee->save();

                    if (isset($payload['work_email']) && $payload['work_email'] !== $oldWorkEmail) {
                        $employee->user()?->update(['email' => $payload['work_email']]);
                    }

                    AuditLogger::log('update', 'employee', $employee->id, $old, $payload);
                }
            });
        } catch (\Throwable $e) {
            if ($newAvatarPath) {
                Storage::disk('local')->delete($newAvatarPath);
            }
            throw $e;
        }

        if (
            $newAvatarPath
            && $oldAvatarPath
            && $oldAvatarPath !== $newAvatarPath
            && !Employee::where('avatar', $oldAvatarPath)->exists()
        ) {
            Storage::disk('local')->delete($oldAvatarPath);
        }

        return redirect()
            ->route('employees.show', $employee)
            ->with('success', 'Đã cập nhật hồ sơ nhân viên thành công.');
    }

    public function resign(Request $request, Employee $employee)
    {
        $actor = $request->user();
        $employee = Employee::visibleTo($actor)->findOrFail($employee->id);
        $this->authorize('update', $employee);

        $data = $request->validate([
            'resignation_date' => ['required', 'date_format:Y-m-d'],
            'resignation_reason' => ['required', 'string', 'max:5000'],
        ]);

        $newData = [
            'employment_status' => 'resigned',
            'resignation_date' => $data['resignation_date'],
            'resignation_reason' => $data['resignation_reason'],
        ];

        if ($actor->needsApproval()) {
            DB::transaction(function () use ($actor, $employee, $newData) {
                $oldData = $employee->only(['employment_status', 'resignation_date', 'resignation_reason']);
                ChangeRequest::create([
                    'entity_type' => 'employee',
                    'entity_id' => $employee->id,
                    'action' => 'update',
                    'old_data' => $oldData,
                    'new_data' => $newData,
                    'status' => 'pending',
                    'requested_by' => $actor->id,
                    'requested_at' => now(),
                ]);

                AuditLogger::log('request_resign', 'employee', $employee->id, $oldData, $newData);
            });

            return redirect()
                ->route('employees.show', $employee)
                ->with('success', 'Đã gửi đề xuất chuyển trạng thái nghỉ việc chờ Giám đốc phê duyệt.');
        }

        DB::transaction(function () use ($employee, $newData) {
            $employee = Employee::lockForUpdate()->findOrFail($employee->id);
            $oldData = $employee->only(['employment_status', 'resignation_date', 'resignation_reason']);

            $employee->update($newData);
            $employee->contracts()->where('status', 'active')->update(['status' => 'terminated']);
            $employee->user()?->update(['status' => 'locked', 'remember_token' => null]);

            AuditLogger::log('resign', 'employee', $employee->id, $oldData, $newData);
        });

        return redirect()
            ->route('employees.show', $employee)
            ->with('success', 'Đã xác nhận nhân viên nghỉ việc, chấm dứt hợp đồng đang hiệu lực và khóa tài khoản.');
    }

    public function approve(Request $request, Employee $employee)
    {
        $actor = $request->user();
        abort_unless($actor->isDirector(), 403, 'Chỉ Giám đốc mới có quyền phê duyệt hồ sơ nhân viên.');

        DB::transaction(function () use ($actor, $employee) {
            $employee = Employee::lockForUpdate()->findOrFail($employee->id);
            $old = ['approval_status' => $employee->approval_status];

            $employee->update([
                'approval_status' => 'approved',
                'approved_by' => $actor->id,
                'approved_at' => now(),
                'rejection_reason' => null,
            ]);

            AuditLogger::log('approve', 'employee', $employee->id, $old, ['approval_status' => 'approved']);
        });

        return back()->with('success', 'Đã phê duyệt hồ sơ nhân viên.');
    }

    public function reject(RejectApprovalRequest $request, Employee $employee)
    {
        $actor = $request->user();
        abort_unless($actor->isDirector(), 403, 'Chỉ Giám đốc mới có quyền từ chối hồ sơ nhân viên.');
        $data = $request->validated();

        DB::transaction(function () use ($actor, $employee, $data) {
            $employee = Employee::lockForUpdate()->findOrFail($employee->id);
            $old = ['approval_status' => $employee->approval_status];

            $employee->update([
                'approval_status' => 'rejected',
                'approved_by' => $actor->id,
                'approved_at' => now(),
                'rejection_reason' => $data['rejection_reason'],
            ]);

            AuditLogger::log('reject', 'employee', $employee->id, $old, [
                'approval_status' => 'rejected',
                'rejection_reason' => $data['rejection_reason'],
            ]);
        });

        return back()->with('success', 'Đã từ chối hồ sơ nhân viên.');
    }

    public function photo(Request $request, Employee $employee): StreamedResponse
    {
        $this->authorize('view', $employee);
        abort_unless($employee->avatar && Storage::disk('local')->exists($employee->avatar), 404);

        return Storage::disk('local')->response($employee->avatar);
    }

    public function destroy(Request $request, Employee $employee)
    {
        $actor = $request->user();
        $employee = Employee::visibleTo($actor)->findOrFail($employee->id);
        $this->authorize('delete', $employee);

        DB::transaction(function () use ($employee) {
            $employee = Employee::lockForUpdate()->findOrFail($employee->id);
            $old = $employee->toArray();
            $employee->delete();
            AuditLogger::log('delete', 'employee', $employee->id, $old, null);
        });

        return redirect()->route('employees.index')->with('success', 'Đã xóa mềm hồ sơ nhân viên.');
    }

    private function options(?Employee $employee = null): array
    {
        return [
            'departments' => Department::where('status', 'active')
                ->where('approval_status', 'approved')
                ->when($employee, fn ($q) => $q->orWhere('id', $employee->department_id))
                ->orderBy('name')
                ->get(),
            'positions' => Position::where('status', 'active')
                ->where('approval_status', 'approved')
                ->when($employee, fn ($q) => $q->orWhere('id', $employee->position_id))
                ->orderBy('name')
                ->get(),
        ];
    }
}
