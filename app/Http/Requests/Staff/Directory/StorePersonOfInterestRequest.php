<?php

namespace App\Http\Requests\Staff\Directory;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePersonOfInterestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'created_by_user_id' => ['nullable', 'uuid', 'exists:users,id'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'display_name' => ['nullable', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date'],
            'date_of_passing' => ['required', 'date', 'after_or_equal:date_of_birth'],
            'place_of_birth' => ['nullable', 'string', 'max:255'],
            'place_of_passing' => ['nullable', 'string', 'max:255'],
            'profile_image_path' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'in:draft,active,archived'],
        ];
    }
}
