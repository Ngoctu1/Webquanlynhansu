<?php

namespace App\Http\Requests;

use App\Models\{Employee, User};
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class SaveEmployeeRequest extends HrmRequest
{
    public function rules(): array
    {
        /** @var Employee|null $employee */
        $employee = $this->route('employee');
        $linkedUserId = $employee?->user?->id;

        return [
            'employee_code' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('employees', 'employee_code')->ignore($employee?->id),
            ],
            'full_name' => ['required', 'string', 'max:150'],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'date_of_birth' => [
                'required',
                'date_format:Y-m-d',
                'before_or_equal:today',
                function ($attribute, $value, $fail) {
                    $hireDate = $this->input('hire_date');
                    if ($value && $hireDate) {
                        try {
                            $dob = Carbon::parse($value);
                            $hire = Carbon::parse($hireDate);
                            if ($dob->copy()->addYears(18)->gt($hire)) {
                                $fail('Nhân viên phải đủ 18 tuổi trở lên tính tại ngày nhận việc.');
                            }
                        } catch (\Throwable) {
                            // Định dạng ngày sẽ được bắt bởi date_format
                        }
                    }
                },
            ],
            'citizen_id' => [
                'required',
                'digits:12',
                Rule::unique('employees', 'citizen_id')->ignore($employee?->id),
            ],
            'phone' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[0-9+() .-]{8,20}$/',
            ],
            'personal_email' => [
                'nullable',
                'email',
                'max:150',
                Rule::unique('employees', 'personal_email')->ignore($employee?->id),
            ],
            'work_email' => [
                'required',
                'email',
                'max:150',
                Rule::unique('employees', 'work_email')->ignore($employee?->id),
                Rule::unique('users', 'email')->ignore($linkedUserId),
            ],
            'address' => ['nullable', 'string', 'max:255'],
            'department_id' => [
                'required',
                'integer',
                Rule::exists('departments', 'id')->where(fn ($q) => $q->whereNull('deleted_at')),
            ],
            'position_id' => [
                'required',
                'integer',
                Rule::exists('positions', 'id')->where(fn ($q) => $q->whereNull('deleted_at')),
            ],
            'hire_date' => ['required', 'date_format:Y-m-d'],
            'annual_leave_days' => ['nullable', 'integer', 'min:0', 'max:255'],
            'employment_status' => ['nullable', Rule::in(['pending', 'working', 'on_leave', 'resigned'])],
            'avatar' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'profile_photo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function payload(): array
    {
        $data = $this->safe()->except(['avatar', 'profile_photo']);
        if (empty($data['annual_leave_days'])) {
            $data['annual_leave_days'] = 12;
        }

        return $data;
    }
}
