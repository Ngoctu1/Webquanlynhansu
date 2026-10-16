<?php

namespace App\Http\Requests;

use App\Models\Employee;
use Illuminate\Validation\Rule;

class SaveEmployeeRequest extends HrmRequest
{
    public function rules(): array
    {
        /** @var Employee|null $employee */
        $employee = $this->route('employee');
        return [
            'employee_code' => $employee ? ['sometimes', 'string', Rule::in([$employee->employee_code])] : ['prohibited'],
            'full_name' => 'required|string|max:150',
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'date_of_birth' => 'nullable|date_format:Y-m-d|before_or_equal:today',
            'phone' => 'nullable|string|max:20',
            'personal_email' => ['required', 'email', 'max:255', Rule::unique('employees', 'personal_email')->ignore($employee?->id)],
            'work_email' => ['nullable', 'email', 'max:255', Rule::unique('employees', 'work_email')->ignore($employee?->id)],
            'address' => 'nullable|string|max:255',
            'department_id' => ['required', 'integer', Rule::exists('departments', 'id')->where(fn ($q) => $q->where(fn ($q) => $q->where('status', 'active')->orWhere('id', $employee?->department_id)))],
            'position_id' => ['required', 'integer', Rule::exists('positions', 'id')->where(fn ($q) => $q->where(fn ($q) => $q->where('status', 'active')->orWhere('id', $employee?->position_id)))],
            'work_schedule_id' => ['required', 'integer', Rule::exists('work_schedules', 'id')->where(fn ($q) => $q->where(fn ($q) => $q->where('status', 'active')->orWhere('id', $employee?->work_schedule_id)))],
            'hire_date' => 'nullable|date_format:Y-m-d',
            'employment_status' => ['required', Rule::in(['pending', 'working', 'on_leave', 'resigned'])],
        ];
    }

    public function payload(): array
    {
        return $this->safe()->except(['employee_code']);
    }
}
