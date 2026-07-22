<?php

namespace App\Http\Requests\Staff\Crm;

use App\Enums\CrmFollowUpType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCrmFollowUpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'assigned_staff_user_id' => ['nullable', 'uuid', 'exists:staff_users,id'],
            'type' => ['required', Rule::enum(CrmFollowUpType::class)],
            'title' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'due_at' => ['required', 'date'],
        ];
    }
}
