<?php

namespace App\Http\Requests\Vault;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateVaultPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'visibility' => ['required', 'string', 'in:all,selected'],
            'beneficiary_ids' => ['required_if:visibility,selected', 'array', 'min:1'],
            'beneficiary_ids.*' => ['uuid'],
        ];
    }
}
