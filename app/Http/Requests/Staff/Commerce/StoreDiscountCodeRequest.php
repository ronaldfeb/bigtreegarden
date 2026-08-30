<?php

namespace App\Http\Requests\Staff\Commerce;

use App\Enums\DiscountType;
use App\Models\ServiceProviderCreditPackage;
use App\Models\SubscriptionPackage;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreDiscountCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'applies_to_all' => $this->boolean('applies_to_all'),
        ]);

        if ($this->boolean('applies_to_all')) {
            $this->merge([
                'discountable_type' => null,
                'discountable_id' => null,
            ]);

            return;
        }

        $target = $this->string('target')->toString();

        if ($target !== '' && str_contains($target, ':')) {
            [$type, $id] = explode(':', $target, 2);

            $this->merge([
                'discountable_type' => $type,
                'discountable_id' => $id,
            ]);
        }
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'discount_type' => ['required', Rule::enum(DiscountType::class)],
            'percent' => ['nullable', 'integer', 'min:1', 'max:100', 'required_if:discount_type,percent'],
            'amount_cents' => ['nullable', 'integer', 'min:1', 'required_if:discount_type,fixed'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'applies_to_all' => ['required', 'boolean'],
            'discountable_type' => [
                'nullable',
                'required_if:applies_to_all,false,0',
                Rule::in([SubscriptionPackage::class, ServiceProviderCreditPackage::class]),
            ],
            'discountable_id' => ['nullable', 'required_if:applies_to_all,false,0', 'uuid'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->boolean('applies_to_all')) {
                return;
            }

            $type = $this->string('discountable_type')->toString();
            $id = $this->string('discountable_id')->toString();

            if ($type === '' || $id === '') {
                return;
            }

            $exists = match ($type) {
                SubscriptionPackage::class => SubscriptionPackage::query()->whereKey($id)->exists(),
                ServiceProviderCreditPackage::class => ServiceProviderCreditPackage::query()->whereKey($id)->exists(),
                default => false,
            };

            if (! $exists) {
                $validator->errors()->add('discountable_id', 'Select a valid package.');
            }
        });
    }
}
