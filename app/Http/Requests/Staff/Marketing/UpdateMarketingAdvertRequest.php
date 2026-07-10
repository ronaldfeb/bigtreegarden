<?php

namespace App\Http\Requests\Staff\Marketing;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMarketingAdvertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'client_name' => ['sometimes', 'required', 'string', 'max:255'],
            'campaign_name' => ['nullable', 'string', 'max:255'],
            'destination_url' => ['sometimes', 'required', 'url'],
            'utm_source' => ['sometimes', 'required', 'string', 'max:255'],
            'utm_medium' => ['nullable', 'string', 'max:255'],
            'utm_campaign' => ['sometimes', 'required', 'string', 'max:255'],
            'utm_content' => ['nullable', 'string', 'max:255'],
            'status' => ['sometimes', 'required', 'string', 'in:active,paused,archived'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
        ];
    }
}
