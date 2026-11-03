<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class SavePositionRequest extends HrmRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'description' => [
                'bail',
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    if (strlen((string) $value) > 65535) {
                        $fail('Mô tả quá dài.');
                    }
                },
            ],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
