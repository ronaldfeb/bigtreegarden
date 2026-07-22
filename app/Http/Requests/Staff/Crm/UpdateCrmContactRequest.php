<?php

namespace App\Http\Requests\Staff\Crm;

use App\Enums\CrmJourney;
use App\Enums\CrmLifecycleStage;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCrmContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'crm_organisation_id' => ['nullable', 'uuid', 'exists:crm_organisations,id'],
            'relationship_owner_staff_user_id' => ['nullable', 'uuid', 'exists:staff_users,id'],
            'user_id' => ['nullable', 'uuid', 'exists:users,id'],
            'marketing_lead_id' => ['nullable', 'uuid', 'exists:marketing_leads,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'role_title' => ['nullable', 'string', 'max:255'],
            'journey' => ['nullable', Rule::enum(CrmJourney::class)],
            'lifecycle_stage' => ['required', Rule::enum(CrmLifecycleStage::class)],
            'notes' => ['nullable', 'string'],
        ];
    }
}
