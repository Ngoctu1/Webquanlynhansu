<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class FilterCatalogRequest extends HrmRequest
{
    public function rules(): array
    {
        return ['q' => 'nullable|string|max:120', 'status' => ['nullable', Rule::in(['active', 'inactive'])]];
    }
}
