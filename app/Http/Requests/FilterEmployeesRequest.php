<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class FilterEmployeesRequest extends HrmRequest
{
    public function rules(): array
    {
        return [
            'q' => 'nullable|string|max:150',
            'department_id' => 'nullable|integer|exists:departments,id',
            'position_id' => 'nullable|integer|exists:positions,id',
            'employment_status' => ['nullable', Rule::in(['pending', 'working', 'on_leave', 'resigned'])],
        ];
    }
}
