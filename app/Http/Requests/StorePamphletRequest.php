<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePamphletRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'heading' => ['required', 'string', 'max:255'],
            'person_full_name' => ['required', 'string', 'max:255'],
            'font_family' => ['nullable', 'string', 'max:120'],
            'date_of_birth' => ['required', 'date', 'before:date_of_passing'],
            'date_of_passing' => ['required', 'date', 'after_or_equal:date_of_birth', 'before_or_equal:today'],
            'date_format' => ['required', 'string', Rule::in(['d M Y', 'd/m/Y', 'Y-m-d', 'j F Y'])],
            'image_shape' => ['required', 'string', Rule::in(['circle', 'square'])],
            'image_crop_mode' => ['required', 'string', Rule::in(['cover', 'contain'])],
            'short_text' => ['required', 'string', 'max:2000'],
            'background_id' => ['required', 'uuid', Rule::exists('backgrounds', 'id')],
            'image' => ['required', 'image', 'max:5120'],
        ];
    }
}
