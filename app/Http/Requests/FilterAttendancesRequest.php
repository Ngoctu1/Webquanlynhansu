<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class FilterAttendancesRequest extends HrmRequest
{
    public function rules(): array
    {
        return ['employee_id' => 'nullable|integer|exists:employees,id',
            'from' => 'nullable|date_format:Y-m-d', 'to' => 'nullable|date_format:Y-m-d|after_or_equal:from',
            'status' => ['nullable', Rule::in(['checked_in', 'completed', 'adjusted'])]];
    }
}
