<?php

namespace App\Http\Requests\Provider;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMemorialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'heading' => ['required', 'string', 'max:255'],
            'person_full_name' => ['required', 'string', 'max:255'],
            'family_contact_name' => ['required', 'string', 'max:255'],
            'family_contact_email' => ['required', 'email', 'max:255'],
            'font_family' => ['nullable', 'string', 'max:120'],
            'date_of_birth' => ['required', 'date', 'before:date_of_passing'],
            'date_of_passing' => ['required', 'date', 'after_or_equal:date_of_birth', 'before_or_equal:today'],
            'date_format' => ['required', 'string', Rule::in(['d M Y', 'd/m/Y', 'Y-m-d', 'j F Y'])],
            'image_shape' => ['required', 'string', Rule::in(['circle', 'square'])],
            'image_crop_mode' => ['required', 'string', Rule::in(['cover', 'contain'])],
            'short_text' => ['required', 'string', 'max:2000'],
            'background_source' => ['required', 'string', Rule::in(['catalogue', 'provider'])],
            'background_id' => [
                'nullable',
                'uuid',
                'required_if:background_source,catalogue',
                Rule::exists('memorial_page_pamphlet_backgrounds', 'id'),
            ],
            'service_provider_background_id' => [
                'nullable',
                'uuid',
                'required_if:background_source,provider',
                Rule::exists('service_provider_backgrounds', 'id'),
            ],
            'image' => ['required', 'image', 'max:5120'],
            'heading_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'name_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'short_text_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'dates_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ];
    }
}
