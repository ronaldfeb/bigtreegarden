<?php

namespace App\Http\Requests\Staff\Content;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateHelpCenterArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'help_center_topic_id' => ['sometimes', 'required', 'uuid', 'exists:help_center_topics,id'],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string'],
            'body' => ['sometimes', 'required', 'string'],
            'status' => ['sometimes', 'required', 'string', 'in:draft,published'],
            'published_at' => ['nullable', 'date'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['uuid', 'exists:help_center_categories,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('published_at')) {
            return;
        }

        $article = $this->route('help_center_article');

        if ($article?->published_at === null) {
            return;
        }

        $this->merge([
            'published_at' => $article->published_at->format('Y-m-d\TH:i'),
        ]);
    }
}
