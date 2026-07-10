<?php

namespace App\Http\Requests\Staff\Marketing;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMarketingLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'staff_user_id' => ['nullable', 'uuid', 'exists:staff_users,id'],
            'marketing_advert_id' => ['nullable', 'uuid', 'exists:marketing_adverts,id'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'organisation' => ['nullable', 'string', 'max:255'],
            'source' => ['sometimes', 'required', 'string', 'in:advert,website,referral,walk_in,other'],
            'status' => ['sometimes', 'required', 'string', 'in:new,contacted,qualified,converted,lost'],
        ];
    }
}
