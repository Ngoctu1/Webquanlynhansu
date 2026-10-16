<?php

namespace App\Http\Requests;

use App\Models\Department;
use Illuminate\Validation\Rule;

class SaveDepartmentRequest extends HrmRequest
{
    public function rules(): array
    {
        /** @var Department|null $model */
        $model = $this->route('department');
        return [
            'code' => ['required', 'string', 'max:20', Rule::unique('departments', 'code')->ignore($model?->id)],
            'name' => ['required', 'string', 'max:120'],
            'description' => ['bail', 'nullable', 'string', function ($attribute, $value, $fail) {
                if (strlen($value) > 65535) { $fail('Mô tả quá dài.'); }
            }],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
