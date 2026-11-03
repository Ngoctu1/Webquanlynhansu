<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ActivateAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['nullable', 'email', 'max:150'],
            'token' => ['nullable', 'string'],
            'password' => [
                'bail',
                'required',
                'string',
                'confirmed',
                'max:72',
                Password::min(8)->mixedCase()->numbers(),
                function ($attribute, $value, $fail) {
                    if (strlen($value) > 72) {
                        $fail('Mật khẩu không được vượt 72 byte.');
                    }
                },
            ],
        ];
    }
}
