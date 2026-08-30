<?php

namespace App\Http\Requests;

use App\Support\PamphletLayout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdatePamphletRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
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
            'background_id' => ['required', 'uuid', Rule::exists('memorial_page_pamphlet_backgrounds', 'id')],
            'image' => ['nullable', 'image', 'max:5120'],
            'heading_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'name_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'short_text_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'dates_color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'layout' => ['nullable'],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                PamphletLayout::validate($validator, $this->input('layout'));
            },
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function validated($key = null, $default = null): mixed
    {
        $validated = parent::validated($key, $default);

        if ($key !== null) {
            return $validated;
        }

        $validated['layout'] = PamphletLayout::normalize(
            PamphletLayout::decode($validated['layout'] ?? null),
        );

        return $validated;
    }
}
