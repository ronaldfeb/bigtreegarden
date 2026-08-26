<?php

namespace App\Http\Requests\Provider;

use App\Enums\ServiceProviderCreditPaymentMethod;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCreditPurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'service_provider_credit_package_id' => ['required', 'uuid', 'exists:service_provider_credit_packages,id'],
            'payment_method' => ['required', Rule::enum(ServiceProviderCreditPaymentMethod::class)],
        ];
    }
}
