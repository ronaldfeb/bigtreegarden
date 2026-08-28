<?php

namespace App\Http\Requests\Staff\Content;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePolicyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'body' => ['sometimes', 'required', 'string'],
            'version' => ['nullable', 'string', 'max:50'],
            'published_at' => ['nullable', 'date'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('published_at')) {
            return;
        }

        $policy = $this->route('policy');

        if ($policy?->published_at === null) {
            return;
        }

        $this->merge([
            'published_at' => $policy->published_at->format('Y-m-d\TH:i'),
        ]);
    }
}
