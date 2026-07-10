<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMemorialPageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'font_family' => ['nullable', 'string', 'max:120'],
            'is_bold' => ['required', 'boolean'],
            'is_italic' => ['required', 'boolean'],
            'date_format' => ['required', 'string', 'max:30'],
            'gallery_images' => ['array'],
            'gallery_images.*' => ['image', 'max:5120'],
            'remove_gallery_image_ids' => ['array'],
            'remove_gallery_image_ids.*' => ['string', 'uuid'],
            'sections' => ['array'],
            'sections.*.id' => ['nullable', 'string', 'uuid'],
            'sections.*.title' => ['required', 'string', 'max:255'],
            'sections.*.body' => ['nullable', 'string'],
            'remove_section_ids' => ['array'],
            'remove_section_ids.*' => ['string', 'uuid'],
        ];
    }
}
