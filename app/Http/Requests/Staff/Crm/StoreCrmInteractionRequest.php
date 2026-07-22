<?php

namespace App\Http\Requests\Staff\Crm;

use App\Enums\CrmInteractionType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCrmInteractionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'subject_type' => ['required', 'string', 'in:organisation,contact'],
            'subject_id' => ['required', 'uuid'],
            'type' => ['required', Rule::enum(CrmInteractionType::class)],
            'summary' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'occurred_at' => ['nullable', 'date'],
        ];
    }
}
