<?php

namespace App\Http\Requests\Staff\Commerce;

use App\Models\SubscriptionPackage;
use App\Models\SubscriptionPackageFeature;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSubscriptionPackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->boolean('sync_features') && ! $this->has('features')) {
            $this->merge(['features' => []]);
        }

        $features = $this->input('features');

        if (! is_array($features)) {
            return;
        }

        $this->merge([
            'features' => array_map(function (mixed $feature): mixed {
                if (! is_array($feature)) {
                    return $feature;
                }

                if (($feature['id'] ?? '') === '') {
                    unset($feature['id']);
                }

                return $feature;
            }, $features),
        ]);
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        $package = $this->route('subscription_package');
        $packageId = $package instanceof SubscriptionPackage ? $package->id : null;

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price_cents' => ['sometimes', 'required', 'integer', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'billing_interval' => ['sometimes', 'required', 'string', 'in:once_off,monthly,annual'],
            'is_featured' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'sync_features' => ['sometimes', 'boolean'],
            'features' => ['array'],
            'features.*.id' => [
                'nullable',
                'uuid',
                Rule::exists(SubscriptionPackageFeature::class, 'id')->where(
                    fn ($query) => $query->where('subscription_package_id', $packageId),
                ),
            ],
            'features.*.label' => ['required', 'string', 'max:255'],
            'features.*.description' => ['nullable', 'string', 'max:255'],
            'features.*.is_included' => ['nullable', 'boolean'],
        ];
    }
}
