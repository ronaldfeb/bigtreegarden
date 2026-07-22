<?php

namespace App\Http\Requests\Staff\Crm;

use App\Enums\CrmOrganisationType;
use App\Enums\CrmPartnerStage;
use App\Enums\CrmRelationshipKind;
use App\Enums\CrmRelationshipStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCrmOrganisationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(CrmOrganisationType::class)],
            'relationship_kind' => ['required', Rule::enum(CrmRelationshipKind::class)],
            'partner_stage' => ['nullable', Rule::enum(CrmPartnerStage::class)],
            'relationship_status' => ['required', Rule::enum(CrmRelationshipStatus::class)],
            'relationship_owner_staff_user_id' => ['nullable', 'uuid', 'exists:staff_users,id'],
            'service_provider_id' => ['nullable', 'uuid', 'exists:service_providers,id'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'geographic_coverage' => ['nullable', 'string', 'max:255'],
            'services_offered' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'next_review_at' => ['nullable', 'date'],
        ];
    }
}
