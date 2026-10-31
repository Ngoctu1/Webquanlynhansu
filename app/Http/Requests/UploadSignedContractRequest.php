<?php

namespace App\Http\Requests;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;

class UploadSignedContractRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->status === 'active' && $this->user()->hasRole(Role::HR);
    }

    public function rules(): array
    {
        return ['signed_document' => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:10240'];
    }
}
