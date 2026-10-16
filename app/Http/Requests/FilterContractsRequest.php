<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class FilterContractsRequest extends HrmRequest
{
    public function rules(): array
    {
        return ['q' => 'nullable|string|max:150', 'status' => ['nullable', Rule::in(['draft', 'pending_approval', 'approved', 'rejected'])]];
    }
}
