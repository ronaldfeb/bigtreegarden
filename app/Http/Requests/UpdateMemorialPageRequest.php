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
            'funeral_programme' => ['nullable', 'string'],
            'obituary' => ['nullable', 'string'],
            'hymns' => ['nullable', 'string'],
            'gallery_images' => ['array'],
            'gallery_images.*' => ['image', 'max:5120'],
            'remove_gallery_image_ids' => ['array'],
            'remove_gallery_image_ids.*' => ['string', 'uuid'],
            'additional_sections' => ['array'],
            'additional_sections.*.title' => ['required', 'string', 'max:255'],
            'additional_sections.*.content' => ['nullable', 'string'],
        ];
    }
}
