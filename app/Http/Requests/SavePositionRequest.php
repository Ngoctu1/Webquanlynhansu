<?php

namespace App\Http\Requests;

use App\Models\Position;
use Illuminate\Validation\Rule;

class SavePositionRequest extends HrmRequest
{
    public function rules(): array
    {
        /** @var Position|null $model */
        $model = $this->route('position');
        return [
            'code' => ['required', 'string', 'max:20', Rule::unique('positions', 'code')->ignore($model?->id)],
            'name' => ['required', 'string', 'max:120'],
            'description' => ['bail', 'nullable', 'string', function ($attribute, $value, $fail) {
                if (strlen($value) > 65535) { $fail('Mô tả quá dài.'); }
            }],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
