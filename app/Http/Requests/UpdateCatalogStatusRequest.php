<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateCatalogStatusRequest extends HrmRequest
{
    public function rules(): array
    {
        return ['status' => ['required', Rule::in(['active', 'inactive'])]];
    }
}
