<?php

namespace App\Http\Requests;

use App\Models\{Contract, Employee};
use Illuminate\Validation\Rule;

class SaveContractRequest extends HrmRequest
{
    protected function prepareForValidation(): void
    {
        if (!$this->has('coefficient') && $this->has('salary_coefficient')) {
            $this->merge([
                'coefficient' => $this->input('salary_coefficient'),
            ]);
        }

        if (!$this->hasFile('pdf_file') && $this->hasFile('signed_document')) {
            $this->files->set('pdf_file', $this->file('signed_document'));
        }
    }

    public function rules(): array
    {
        /** @var Contract|null $contract */
        $contract = $this->route('contract');
        $actor = $this->user();
        $isDirectorCreating = !$contract && $actor && $actor->isDirector();

        return [
            'contract_code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('contracts', 'contract_code')->ignore($contract?->id),
            ],
            'employee_id' => [
                'required',
                'integer',
                'exists:employees,id',
                function (string $attribute, mixed $value, \Closure $fail) use ($contract, $actor) {
                    if ($contract && (int) $value !== (int) $contract->employee_id) {
                        $fail('Không được thay đổi nhân viên của hợp đồng đã tạo.');
                        return;
                    }

                    $employee = Employee::find($value);
                    if (!$employee) {
                        $fail('Nhân viên không tồn tại.');
                        return;
                    }

                    if ($employee->approval_status !== 'approved') {
                        $fail('Chỉ được tạo hợp đồng cho nhân viên có hồ sơ đã được Giám đốc phê duyệt.');
                        return;
                    }

                    if ($employee->isAdminOrDirector()) {
                        $fail('Tài khoản Admin và Giám đốc không cần hợp đồng lao động.');
                        return;
                    }

                    if ($actor && $actor->employee_id && (int) $actor->employee_id === (int) $employee->id) {
                        $fail('Không được tự tạo hoặc chỉnh sửa hợp đồng của chính mình.');
                    }
                },
            ],
            'previous_contract_id' => [
                'nullable',
                'integer',
                'exists:contracts,id',
                function (string $attribute, mixed $value, \Closure $fail) use ($contract) {
                    if (!$value) {
                        return;
                    }

                    if ($contract && (int) $value === (int) $contract->id) {
                        $fail('Hợp đồng liền trước không thể là chính hợp đồng này.');
                        return;
                    }

                    $prev = Contract::find($value);
                    $empId = (int) $this->input('employee_id', $contract?->employee_id);
                    if ($prev && (int) $prev->employee_id !== $empId) {
                        $fail('Hợp đồng liền trước phải thuộc về cùng một nhân viên.');
                    }
                },
            ],
            'contract_type' => ['required', Rule::in(['probation', 'fixed_term', 'indefinite'])],
            'signed_date' => ['nullable', 'date'],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'base_salary' => ['required', 'numeric', 'min:0', 'max:9999999999999.99'],
            'coefficient' => ['required', 'numeric', 'gt:0', 'max:999.99'],
            'allowance' => ['required', 'numeric', 'min:0', 'max:9999999999999.99'],
            'allowance_note' => ['nullable', 'string', 'max:2000'],
            'pdf_file' => [
                $isDirectorCreating ? 'required' : 'nullable',
                'file',
                'mimes:pdf',
                'max:10240',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'contract_code.unique' => 'Mã hợp đồng này đã tồn tại trong hệ thống.',
            'effective_to.after_or_equal' => 'Ngày hết hạn phải sau hoặc bằng ngày bắt đầu hiệu lực.',
            'pdf_file.required' => 'Giám đốc tạo hợp đồng trực tiếp bắt buộc phải đính kèm file PDF hợp đồng đã ký giấy.',
            'pdf_file.mimes' => 'File hợp đồng đính kèm bắt buộc phải có định dạng PDF.',
            'pdf_file.max' => 'Dung lượng file PDF tối đa là 10MB.',
        ];
    }

    public function payload(): array
    {
        $data = $this->safe()->except(['pdf_file']);
        if (($data['contract_type'] ?? null) === 'indefinite' && empty($data['effective_to'])) {
            $data['effective_to'] = null;
        }
        return $data;
    }
}
