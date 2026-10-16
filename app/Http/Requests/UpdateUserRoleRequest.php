<?php

namespace App\Http\Requests;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        return $user !== null && $user->status === 'active' && $user->hasRole(Role::ADMIN);
    }

    public function rules(): array
    {
        return [
            'role_id' => ['required', 'integer', Rule::exists('roles', 'id')->whereIn('name', [Role::HR, Role::DIRECTOR, Role::EMPLOYEE, Role::ADMIN])],
        ];
    }
}
