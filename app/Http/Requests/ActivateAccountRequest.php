<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ActivateAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Preserve the expired/used-token response before validating a new password.
        $token = (string) $this->route('token');
        abort_unless(preg_match('/^[A-Za-z0-9]{64}$/D', $token), 410, 'Liên kết không hợp lệ.');
        abort_unless(User::where('activation_token_hash', hash('sha256', $token))
            ->where('status', 'pending_reset')->whereNull('activated_at')
            ->where('activation_expires_at', '>', now())->exists(), 410,
            'Liên kết không hợp lệ, đã dùng hoặc đã hết hạn.');
        return true;
    }

    public function rules(): array
    {
        return ['password' => ['bail', 'required', 'string', 'confirmed', 'max:72', Password::min(8)->mixedCase()->numbers(), function ($attribute, $value, $fail) {
            if (strlen($value) > 72) { $fail('Mật khẩu không được vượt 72 byte.'); }
        }]];
    }
}
