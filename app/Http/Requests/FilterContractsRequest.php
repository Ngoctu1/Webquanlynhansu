<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class FilterContractsRequest extends HrmRequest
{
    public function rules(): array
    {
        return [
            'q' => 'nullable|string|max:150',
            'status' => [
                'nullable',
                Rule::in(['current', 'active', 'pending_approval', 'rejected', 'expired', 'terminated', 'old', 'all']),
            ],
            'contract_type' => [
                'nullable',
                Rule::in(['probation', 'fixed_term', 'indefinite']),
            ],
            'expiring' => [
                'nullable',
                Rule::in(['1', '2', '30', '60']),
            ],
        ];
    }
}
