<?php

namespace App\Http\Requests;

use App\Models\Contract;
use Illuminate\Validation\Rule;

class SaveContractRequest extends HrmRequest
{
    public function rules(): array
    {
        /** @var Contract|null $contract */
        $contract = $this->route('contract');
        return [
            'contract_code' => $contract ? ['sometimes', 'string', Rule::in([$contract->contract_code])] : ['prohibited'],
            'employee_id' => 'required|integer|exists:employees,id',
            'previous_contract_id' => 'nullable|integer|exists:contracts,id',
            'contract_type' => ['required', Rule::in(['probation', 'fixed_term', 'indefinite'])],
            'signed_date' => 'nullable|date_format:Y-m-d',
            'effective_from' => 'required|date_format:Y-m-d',
            'effective_to' => 'nullable|date_format:Y-m-d|after_or_equal:effective_from',
            'base_salary' => 'required|numeric|decimal:0,2|min:0|max:9999999999999.99',
            'salary_coefficient' => 'required|numeric|decimal:0,3|gt:0|max:999.999',
            'allowance' => 'required|numeric|decimal:0,2|min:0|max:9999999999999.99',
            'status' => 'prohibited', 'created_by' => 'prohibited', 'approved_by' => 'prohibited',
        ];
    }

    public function payload(): array
    {
        return $this->safe()->except(['contract_code', 'status', 'created_by', 'approved_by']);
    }
}
