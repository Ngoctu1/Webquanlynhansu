<?php

namespace App\Http\Requests;

use App\Models\Attendance;
use Illuminate\Validation\Rule;

class SaveAttendanceRequest extends HrmRequest
{
    public function rules(): array
    {
        /** @var Attendance|null $attendance */
        $attendance = $this->route('attendance');
        return [
            'employee_id' => $attendance ? 'prohibited' : 'required|integer|exists:employees,id',
            'work_date' => $attendance ? 'prohibited' : 'required|date_format:Y-m-d|before_or_equal:today',
            'check_in' => 'nullable|required_with:check_out|date_format:Y-m-d\TH:i|before_or_equal:now',
            'check_out' => 'nullable|date_format:Y-m-d\TH:i|after_or_equal:check_in|before_or_equal:now',
            'late_minutes' => 'required|integer|min:0|max:4294967295',
            'regular_minutes' => 'required|integer|between:0,480',
            'overtime_minutes' => 'required|integer|between:0,960',
            'overtime_type' => ['nullable', 'required_if:overtime_status,approved,rejected', Rule::in(['weekday', 'rest_day', 'holiday'])],
            'overtime_status' => ['required', Rule::in(['none', 'approved', 'rejected'])],
            'adjustment_reason' => 'required|string|max:5000',
        ];
    }

    public function payload(): array
    {
        return $this->route('attendance')
            ? $this->safe()->except(['employee_id', 'work_date']) : $this->validated();
    }
}
