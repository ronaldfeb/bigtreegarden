<?php

namespace App\Http\Requests\Staff\Crm;

use App\Enums\CrmFollowUpType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCrmFollowUpRequest extends FormRequest
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
            'assigned_staff_user_id' => ['nullable', 'uuid', 'exists:staff_users,id'],
            'type' => ['required', Rule::enum(CrmFollowUpType::class)],
            'title' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'due_at' => ['required', 'date'],
        ];
    }
}
