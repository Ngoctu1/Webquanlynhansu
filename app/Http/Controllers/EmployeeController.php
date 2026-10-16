<?php

namespace App\Http\Controllers;

use App\Http\Requests\{FilterEmployeesRequest, SaveEmployeeRequest};

use App\Models\{Department, Employee, Position, WorkSchedule};
use App\Services\{AuditService, RecordCodeService};
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    public function index(FilterEmployeesRequest $request)
    {
        $filters = $request->validated();
        $employees = Employee::with(['department', 'position', 'workSchedule', 'user.role'])
            ->when(isset($filters['q']), function ($q) use ($filters) {
                $q->where(function ($q) use ($filters) {
                    foreach (['employee_code', 'full_name', 'personal_email', 'work_email', 'phone'] as $field) {
                        $q->orWhere($field, 'like', '%'.$filters['q'].'%');
                    }
                });
            });
        foreach (['department_id', 'position_id', 'employment_status'] as $field) {
            if (!empty($filters[$field])) { $employees->where($field, $filters[$field]); }
        }
        $employees = $employees->orderByDesc('id')->paginate(15)->withQueryString();
        return view('admin.employee.index', [
            'employees' => $employees, 'filters' => $filters,
            'departments' => Department::orderBy('name')->get(),
            'positions' => Position::orderBy('name')->get(),
        ]);
    }

    public function create() { return view('admin.employee.create', $this->options()); }

    public function store(SaveEmployeeRequest $request, RecordCodeService $codes)
    {
        $data = $request->payload();
        $employee = $codes->employee(function (string $code) use ($data) {
            $employee = Employee::create([...$data, 'employee_code' => $code]);
            AuditService::record('EMPLOYEE_CREATED', $employee);
            return $employee;
        });
        return redirect()->route('employees.show', $employee)->with('success', 'Đã tạo hồ sơ nhân viên.');
    }

    public function show(Employee $employee)
    {
        $employee->load(['department', 'position', 'user.role']);
        $hasApprovedContract = $employee->contracts()->where('status', 'approved')->exists();
        return view('admin.employee.show', compact('employee', 'hasApprovedContract'));
    }

    public function edit(Employee $employee)
    {
        return view('admin.employee.edit', ['employee' => $employee, ...$this->options($employee)]);
    }

    public function update(SaveEmployeeRequest $request, Employee $employee)
    {
        DB::transaction(function () use ($request, $employee) {
            $employee = Employee::lockForUpdate()->findOrFail($employee->id);
            $old = $employee->getAttributes();
            $employee->fill($request->payload());
            if ($employee->isDirty('work_schedule_id') && $employee->attendances()->exists()) {
                throw \Illuminate\Validation\ValidationException::withMessages(['work_schedule_id' => 'Đã có chấm công theo lịch này. Chuyển lịch cần mô hình lịch sử phân ca; không đổi hồi tố.']);
            }
            if ($employee->isDirty()) {
                $employee->save();
                AuditService::record('EMPLOYEE_UPDATED', $employee, $old);
            }
        });
        return redirect()->route('employees.show', $employee)->with('success', 'Đã cập nhật hồ sơ. Email đăng nhập hiện có không tự thay đổi.');
    }

    public function destroy(Employee $employee)
    {
        DB::transaction(function () use ($employee) {
            $employee = Employee::lockForUpdate()->findOrFail($employee->id);
            $old = $employee->getAttributes();
            if ($employee->employment_status !== 'resigned') {
                $employee->update(['employment_status' => 'resigned']);
                AuditService::record('EMPLOYEE_RESIGNED', $employee, $old);
            }
        });
        return redirect()->route('employees.index')->with('success', 'Đã đánh dấu nghỉ việc; lịch sử được giữ nguyên.');
    }

    private function options(?Employee $employee = null): array
    {
        return [
            'workSchedules' => WorkSchedule::where('status', 'active')->when($employee, fn ($q) => $q->orWhere('id', $employee->work_schedule_id))->orderBy('name')->get(),
            'departments' => Department::where('status', 'active')->when($employee, fn ($q) => $q->orWhere('id', $employee->department_id))->orderBy('name')->get(),
            'positions' => Position::where('status', 'active')->when($employee, fn ($q) => $q->orWhere('id', $employee->position_id))->orderBy('name')->get(),
        ];
    }

}
