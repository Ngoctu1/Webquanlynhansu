<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReviewLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->status === 'active' && $this->user()->canManageHrm();
    }

    public function rules(): array
    {
        return ['rejection_reason' => 'required|string|max:5000'];
    }
}
