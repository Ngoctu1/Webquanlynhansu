<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'position_id' => ['nullable', 'integer', 'exists:positions,id'],
            'status' => ['nullable', Rule::in(['working', 'resigned', 'inactive'])],
        ]);
        $query = Employee::with(['department', 'position', 'user.role']);
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($query) use ($search) {
                $query->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('employee_code', 'like', "%{$search}%");
            });
        }
        foreach (['department_id', 'position_id', 'status'] as $field) {
            if (!empty($filters[$field])) {
                $query->where($field, $filters[$field]);
            }
        }
        $employees = $query->latest('id')->paginate(15)->withQueryString();
        $departments = Department::orderBy('name')->get();
        $positions = Position::orderBy('name')->get();

        return view('admin.employee.index', compact('employees', 'departments', 'positions'));
    }

    public function create()
    {
        $departments = Department::where('status', 'active')->orderBy('name')->get();
        $positions = Position::where('status', 'active')->orderBy('name')->get();

        return view('admin.employee.create', compact('departments', 'positions'));
    }

    public function store(Request $request)
    {
        DB::transaction(function () use ($request) {
            $employee = Employee::create($this->validateEmployee($request));
            $employee->assignments()->create([
                'department_id' => $employee->department_id,
                'position_id' => $employee->position_id,
                'effective_from' => $employee->hire_date ?? today(),
                'changed_by' => $request->user()->id,
                'note' => 'Phân công ban đầu.',
            ]);
            $this->audit($request, 'CREATE', $employee, null, $employee->getAttributes());
        });

        return redirect()->route('employees.index')->with('success', 'Thêm nhân viên thành công!');
    }

    public function show(Employee $employee)
    {
        $employee->load(['department', 'position', 'user.role']);
        $department = $employee->department;
        $position = $employee->position;

        return view('admin.employee.show', compact('employee', 'department', 'position'));
    }

    public function edit(Employee $employee)
    {
        // Giữ lựa chọn hiện tại kể cả khi danh mục đã ngừng hoạt động.
        $departments = Department::where('status', 'active')
            ->orWhere('id', $employee->department_id)->orderBy('name')->get();
        $positions = Position::where('status', 'active')
            ->orWhere('id', $employee->position_id)->orderBy('name')->get();

        return view('admin.employee.edit', compact('employee', 'departments', 'positions'));
    }

    public function update(Request $request, Employee $employee)
    {
        DB::transaction(function () use ($request, $employee) {
            $employee = Employee::whereKey($employee->id)->lockForUpdate()->firstOrFail();
            $oldValues = $employee->getAttributes();
            $employee->fill($this->validateEmployee($request, $employee));
            if ($employee->isDirty(['department_id', 'position_id'])) {
                $this->changeAssignment($request, $employee);
            }
            // Link đã gửi tới email cũ không được kích hoạt sau khi thay đổi email.
            if ($employee->isDirty('email')) {
                $user = $employee->user()->lockForUpdate()->first();
                if ($user && $user->status === 'pending') {
                    throw ValidationException::withMessages([
                        'email' => 'Tài khoản đang chờ kích hoạt. Chưa thể đổi email nhận lời mời.',
                    ]);
                }
            }
            $employee->save();
            $this->audit($request, 'UPDATE', $employee, $oldValues, $employee->getAttributes());
        });

        return redirect()->route('employees.index')->with('success', 'Cập nhật nhân viên thành công!');
    }

    public function destroy(Request $request, Employee $employee)
    {
        DB::transaction(function () use ($request, $employee) {
            $employee = Employee::whereKey($employee->id)->lockForUpdate()->firstOrFail();
            $oldValues = $employee->getAttributes();
            $employee->delete();
            $this->audit($request, 'DELETE', $employee, $oldValues, $employee->getAttributes());
        });

        return redirect()->route('employees.index')->with('success', 'Đã xóa nhân viên; lịch sử nhân sự được giữ lại.');
    }

    private function validateEmployee(Request $request, ?Employee $employee = null): array
    {
        $departmentRule = Rule::exists('departments', 'id')->where(function ($query) use ($employee) {
            $query->where(function ($query) use ($employee) {
                $query->where('status', 'active');
                if ($employee?->department_id) {
                    $query->orWhere('id', $employee->department_id);
                }
            });
        });
        $positionRule = Rule::exists('positions', 'id')->where(function ($query) use ($employee) {
            $query->where(function ($query) use ($employee) {
                $query->where('status', 'active');
                if ($employee?->position_id) {
                    $query->orWhere('id', $employee->position_id);
                }
            });
        });

        return $request->validate([
            'employee_code' => ['required', 'string', 'max:20', Rule::unique('employees', 'employee_code')->ignore($employee?->id)],
            'full_name' => ['required', 'string', 'max:150'],
            'date_of_birth' => ['nullable', 'date_format:Y-m-d'],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:150', Rule::unique('employees', 'email')->ignore($employee?->id)],
            'address' => ['nullable', 'string', 'max:255'],
            'identity_number' => ['nullable', 'string', 'max:20', Rule::unique('employees', 'identity_number')->ignore($employee?->id)],
            'hire_date' => ['nullable', 'date_format:Y-m-d'],
            'department_id' => ['nullable', 'integer', $departmentRule],
            'position_id' => ['nullable', 'integer', $positionRule],
            'status' => ['required', Rule::in(['working', 'resigned', 'inactive'])],
        ], [
            'employee_code.unique' => 'Mã nhân viên đã tồn tại.',
            'email.required' => 'Vui lòng nhập email của nhân viên.',
            'email.unique' => 'Email đã được sử dụng.',
            'identity_number.unique' => 'Số CCCD / CMND đã được sử dụng.',
            'department_id.exists' => 'Phòng ban không hợp lệ hoặc đã ngừng hoạt động.',
            'position_id.exists' => 'Chức vụ không hợp lệ hoặc đã ngừng hoạt động.',
        ]);
    }

    private function changeAssignment(Request $request, Employee $employee): void
    {
        $dates = $request->validate([
            'assignment_effective_from' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
        ]);
        $from = Carbon::parse($dates['assignment_effective_from'])->startOfDay();
        $latest = $employee->assignments()->orderByDesc('effective_from')->orderByDesc('id')
            ->lockForUpdate()->first();

        // DATE chỉ biểu diễn một phân công mỗi ngày; tránh khoảng ngày âm/chồng lấn.
        if (($employee->hire_date && $from->lt($employee->hire_date)) ||
            ($latest && ($from->lte($latest->effective_from) ||
                ($latest->effective_to && $from->lte($latest->effective_to))))) {
            throw ValidationException::withMessages([
                'assignment_effective_from' => 'Ngày hiệu lực phải từ ngày vào làm, sau ngày bắt đầu phân công hiện tại và không chồng lấn lịch sử.',
            ]);
        }

        $openAssignments = $employee->assignments()->whereNull('effective_to')->lockForUpdate()->get();
        foreach ($openAssignments as $assignment) {
            $assignment->update(['effective_to' => $from->copy()->subDay()]);
        }
        $employee->assignments()->create([
            'department_id' => $employee->department_id,
            'position_id' => $employee->position_id,
            'effective_from' => $from,
            'changed_by' => $request->user()->id,
            'note' => 'Cập nhật phòng ban hoặc chức vụ.',
        ]);
    }

    private function audit(Request $request, string $action, Employee $employee, ?array $oldValues, array $newValues): void
    {
        AuditLog::create([
            'actor_user_id' => $request->user()->id,
            'action' => $action,
            'entity_type' => Employee::class,
            'entity_id' => $employee->id,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);
    }
}
