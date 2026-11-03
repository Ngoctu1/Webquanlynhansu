<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        return $user !== null
            && $user->status === 'active'
            && !$user->isAdmin()
            && $user->employee_id !== null;
    }

    public function rules(): array
    {
        $leaveType = $this->input('leave_type');
        $isDirector = (bool) $this->user()?->isDirector();

        return [
            'leave_type' => ['required', Rule::in(['annual', 'sick', 'personal', 'unpaid'])],
            'start_date' => array_values(array_filter([
                'required',
                'date',
                ($leaveType === 'sick' || $isDirector) ? null : 'after_or_equal:today',
            ])),
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'max:5000'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'start_date.after_or_equal' => 'Ngày bắt đầu nghỉ phải từ ngày hôm nay trở đi (trừ trường hợp nghỉ ốm).',
            'end_date.after_or_equal' => 'Ngày kết thúc phải sau hoặc bằng ngày bắt đầu nghỉ.',
            'reason.required' => 'Vui lòng nhập lý do xin nghỉ phép.',
            'attachment.mimes' => 'File giấy xác nhận chỉ hỗ trợ định dạng PDF, JPG, JPEG, PNG.',
            'attachment.max' => 'Dung lượng file đính kèm tối đa 5MB.',
        ];
    }
}
