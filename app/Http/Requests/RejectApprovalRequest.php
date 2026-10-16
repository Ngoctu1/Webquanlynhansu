<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RejectApprovalRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        return $user !== null && $user->status === 'active' && $user->canApprove();
    }

    public function rules(): array
    {
        return ['rejection_reason' => 'required|string|max:5000'];
    }
}
