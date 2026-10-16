<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class StorePayrollPeriodRequest extends HrmRequest
{
    public function rules(): array
    {
        return [
            'year' => 'required|integer|between:2000,2100',
            'month' => ['required', 'integer', 'between:1,12', Rule::unique('payroll_periods', 'month')->where('year', $this->input('year'))],
            'work_schedule_id' => ['required', 'integer', Rule::exists('work_schedules', 'id')->where('status', 'active')],
        ];
    }
}
