<?php

namespace App\Http\Requests;

class CalculatePayrollRequest extends HrmRequest
{
    public function rules(): array
    {
        return [
            'attendance_confirmed' => 'accepted', 'adjustments' => 'required|array',
            'adjustments.*' => 'array:deduction,note',
            'adjustments.*.deduction' => 'required|numeric|decimal:0,2|min:0|max:9999999999999.99',
            'adjustments.*.note' => 'nullable|string|max:255',
            'overtime' => 'nullable|array', 'overtime.*' => 'array:hourly_rate,multiplier,reason,confirmed',
            'overtime.*.hourly_rate' => 'required|numeric|decimal:0,2|gt:0|max:9999999999999.99',
            'overtime.*.multiplier' => 'required|numeric|decimal:0,3|min:1.5|max:999.999',
            'overtime.*.reason' => 'nullable|string|max:1000',
            'overtime.*.confirmed' => 'accepted',
        ];
    }
}
