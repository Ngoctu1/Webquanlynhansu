<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->status === 'active' && $this->user()->employee_id !== null;
    }

    public function rules(): array
    {
        return [
            'start_date' => 'required|date_format:Y-m-d|after_or_equal:today',
            'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
            'leave_type' => ['required', Rule::in(['annual', 'sick', 'personal', 'unpaid'])],
            'reason' => 'required|string|max:5000',
        ];
    }
}
