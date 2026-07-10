<?php

namespace App\Http\Requests\Vault;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreVaultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'person_of_interest_id' => ['required', 'uuid', 'exists:persons_of_interest,id'],
            'name' => ['nullable', 'string', 'max:255'],
        ];
    }
}
