<?php

namespace App\Http\Requests;

class FilterAuditLogsRequest extends HrmRequest
{
    public function rules(): array
    {
        return ['action' => 'nullable|string|max:50', 'user_id' => 'nullable|integer|exists:users,id'];
    }
}
