<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HrmRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        return $user !== null && $user->status === 'active' && $user->canManageHrm();
    }
}
