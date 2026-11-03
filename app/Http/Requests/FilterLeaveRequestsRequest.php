<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FilterLeaveRequestsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->status === 'active' && $this->user()->hasManagementAccess();
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::in(['pending', 'approved', 'rejected', 'cancelled', 'expired'])],
            'leave_type' => ['nullable', Rule::in(['annual', 'sick', 'personal', 'unpaid'])],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'week' => ['nullable', 'string', 'max:20'],
            'q' => ['nullable', 'string', 'max:150'],
            'created_from' => ['nullable', 'date'],
            'created_to' => ['nullable', 'date', 'after_or_equal:created_from'],
        ];
    }
}
