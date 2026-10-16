<?php

namespace App\Http\Requests;

use Illuminate\Validation\{Rule, ValidationException};

class SaveWorkScheduleRequest extends HrmRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'start_time' => 'required|date_format:H:i|after_or_equal:06:00',
            'break_start' => 'required|date_format:H:i|after:start_time',
            'break_end' => 'required|date_format:H:i|after:break_start',
            'end_time' => 'required|date_format:H:i|after:break_end|before_or_equal:22:00',
            'late_grace_minutes' => 'required|integer|between:0,30',
            'work_days' => 'required|array|min:1|max:7', 'work_days.*' => 'required|integer|distinct|between:1,7',
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }

    public function payload(): array
    {
        $data = $this->validated();
        $minutes = fn ($time) => ((int) substr($time, 0, 2)) * 60 + (int) substr($time, 3, 2);
        $data['standard_minutes'] = $minutes($data['end_time']) - $minutes($data['start_time']) - $minutes($data['break_end']) + $minutes($data['break_start']);
        if ($data['standard_minutes'] > 480 || $data['standard_minutes'] <= $data['late_grace_minutes']) {
            throw ValidationException::withMessages(['end_time' => 'Ca ngày phải có thời gian làm việc từ trên mức cho phép đi muộn đến tối đa 480 phút.']);
        }
        $data['work_days'] = array_map('intval', $data['work_days']); sort($data['work_days']);
        foreach (['start_time','end_time','break_start','break_end'] as $field) { $data[$field] .= ':00'; }
        return $data;
    }
}
