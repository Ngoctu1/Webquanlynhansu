<?php

namespace App\Http\Requests;

class SendAccountInvitationRequest extends HrmRequest
{
    public function rules(): array
    {
        return ['password' => 'prohibited', 'role_id' => 'prohibited', 'email' => 'prohibited'];
    }
}
