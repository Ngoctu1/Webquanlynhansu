<?php

namespace App\Http\Controllers;

use App\Http\Requests\{FilterEmployeesRequest, SaveEmployeeRequest};

use App\Models\{Department, Employee, Position};
use App\Services\{AuditService, RecordCodeService};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmployeeController extends Controller
{
    public function index(FilterEmployeesRequest $request)
    {
        $filters = $request->validated();
        $employees = Employee::with(['department', 'position', 'user.role'])
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
        $photoPath = $request->file('profile_photo')?->store('employee-profiles', 'local');
        try {
            $employee = $codes->employee(function (string $code) use ($data, $photoPath) {
                $employee = Employee::create([...$data, 'employee_code' => $code, 'profile_photo_path' => $photoPath]);
                AuditService::record('EMPLOYEE_CREATED', $employee);
                return $employee;
            });
        } catch (\Throwable $e) {
            if ($photoPath) { Storage::disk('local')->delete($photoPath); }
            throw $e;
        }
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
        $newPhotoPath = $request->file('profile_photo')?->store('employee-profiles', 'local');
        $oldPhotoPath = null;
        try {
        DB::transaction(function () use ($request, $employee, $newPhotoPath, &$oldPhotoPath) {
            $employee = Employee::lockForUpdate()->findOrFail($employee->id);
            $old = $employee->getAttributes();
            $oldPhotoPath = $employee->profile_photo_path;
            $employee->fill([...$request->payload(), ...($newPhotoPath ? ['profile_photo_path' => $newPhotoPath] : [])]);
            if ($employee->isDirty()) {
                $employee->save();
                AuditService::record('EMPLOYEE_UPDATED', $employee, $old);
            }
        });
        } catch (\Throwable $e) {
            if ($newPhotoPath) { Storage::disk('local')->delete($newPhotoPath); }
            throw $e;
        }
        if ($newPhotoPath && $oldPhotoPath && $oldPhotoPath !== $newPhotoPath
            && !Employee::where('profile_photo_path', $oldPhotoPath)->exists()) {
            Storage::disk('local')->delete($oldPhotoPath);
        }
        return redirect()->route('employees.show', $employee)->with('success', 'Đã cập nhật hồ sơ. Email đăng nhập hiện có không tự thay đổi.');
    }

    public function photo(Employee $employee): StreamedResponse
    {
        $user = request()->user();
        abort_unless($user->canManageHrm() || (int) $user->employee_id === (int) $employee->id, 403);
        abort_unless($employee->profile_photo_path && Storage::disk('local')->exists($employee->profile_photo_path), 404);
        return Storage::disk('local')->response($employee->profile_photo_path);
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
            'departments' => Department::where('status', 'active')->when($employee, fn ($q) => $q->orWhere('id', $employee->department_id))->orderBy('name')->get(),
            'positions' => Position::where('status', 'active')->when($employee, fn ($q) => $q->orWhere('id', $employee->position_id))->orderBy('name')->get(),
        ];
    }

}
