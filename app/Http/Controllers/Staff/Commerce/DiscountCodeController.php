<?php

namespace App\Http\Controllers\Staff\Commerce;

use App\Enums\DiscountType;
use App\Http\Controllers\Staff\Controller;
use App\Http\Requests\Staff\Commerce\StoreDiscountCodeRequest;
use App\Models\DiscountCode;
use App\Models\ServiceProviderCreditPackage;
use App\Models\SubscriptionPackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class DiscountCodeController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('manage-commerce');

        $codes = DiscountCode::query()
            ->with('discountable')
            ->latest()
            ->paginate(20)
            ->through(fn (DiscountCode $code): array => $this->listPayload($code));

        return Inertia::render('staff/commerce/discount-codes/Index', [
            'codes' => $codes,
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('manage-commerce');

        return Inertia::render('staff/commerce/discount-codes/Create', [
            'targets' => $this->targetOptions(),
        ]);
    }

    public function store(StoreDiscountCodeRequest $request): RedirectResponse
    {
        Gate::authorize('manage-commerce');

        $validated = $request->validated();
        $type = DiscountType::from($validated['discount_type']);

        $code = DiscountCode::query()->create([
            'code' => DiscountCode::generate(),
            'discount_type' => $type,
            'percent' => $type === DiscountType::Percent ? $validated['percent'] : null,
            'amount_cents' => $type === DiscountType::Fixed ? $validated['amount_cents'] : null,
            'starts_at' => $validated['starts_at'],
            'ends_at' => $validated['ends_at'],
            'applies_to_all' => $request->boolean('applies_to_all'),
            'discountable_type' => $request->boolean('applies_to_all') ? null : $validated['discountable_type'],
            'discountable_id' => $request->boolean('applies_to_all') ? null : $validated['discountable_id'],
            'created_by_staff_user_id' => $request->user()?->staffUser?->id,
        ]);

        return redirect()->route('staff.commerce.discount-codes.show', $code);
    }

    public function show(DiscountCode $discountCode): Response
    {
        Gate::authorize('manage-commerce');

        $discountCode->load(['discountable', 'createdByStaffUser.user', 'usedByUser', 'reservedTransaction', 'usedTransaction']);

        return Inertia::render('staff/commerce/discount-codes/Show', [
            'code' => $this->detailPayload($discountCode),
        ]);
    }

    public function destroy(DiscountCode $discountCode): RedirectResponse
    {
        Gate::authorize('manage-commerce');

        abort_if($discountCode->used_at !== null, 422, 'Used discount codes cannot be deleted.');

        $discountCode->delete();

        return redirect()->route('staff.commerce.discount-codes.index');
    }

    /**
     * @return array<string, mixed>
     */
    private function listPayload(DiscountCode $code): array
    {
        return [
            'id' => $code->id,
            'formatted_code' => $code->formattedCode(),
            'discount_type' => $code->discount_type->value,
            'value_label' => $this->valueLabel($code),
            'starts_at' => $code->starts_at?->toIso8601String(),
            'ends_at' => $code->ends_at?->toIso8601String(),
            'target_label' => $this->targetLabel($code),
            'status' => $code->statusLabel(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function detailPayload(DiscountCode $code): array
    {
        return [
            ...$this->listPayload($code),
            'code' => $code->code,
            'percent' => $code->percent,
            'amount_cents' => $code->amount_cents,
            'applies_to_all' => $code->applies_to_all,
            'reserved_at' => $code->reserved_at?->toIso8601String(),
            'used_at' => $code->used_at?->toIso8601String(),
            'used_by' => $code->usedByUser?->only(['id', 'name', 'email']),
            'created_by' => $code->createdByStaffUser?->user?->only(['id', 'name', 'email']),
            'created_at' => $code->created_at?->toIso8601String(),
        ];
    }

    private function valueLabel(DiscountCode $code): string
    {
        return match ($code->discount_type) {
            DiscountType::Percent => $code->percent.'%',
            DiscountType::Fixed => 'R'.number_format(($code->amount_cents ?? 0) / 100, 2),
        };
    }

    private function targetLabel(DiscountCode $code): string
    {
        if ($code->applies_to_all) {
            return 'All packages';
        }

        $target = $code->discountable;

        if ($target instanceof SubscriptionPackage) {
            return 'Subscription: '.$target->name;
        }

        if ($target instanceof ServiceProviderCreditPackage) {
            return 'Credits: '.$target->name;
        }

        return 'Unknown';
    }

    /**
     * @return list<array{group: string, options: list<array{value: string, label: string}>}>
     */
    private function targetOptions(): array
    {
        $subscriptionOptions = SubscriptionPackage::query()
            ->active()
            ->orderBy('sort_order')
            ->get()
            ->map(fn (SubscriptionPackage $package): array => [
                'value' => SubscriptionPackage::class.':'.$package->id,
                'label' => $package->name.' ('.$package->billing_interval.')',
            ])
            ->values()
            ->all();

        $creditOptions = ServiceProviderCreditPackage::query()
            ->active()
            ->orderBy('sort_order')
            ->get()
            ->map(fn (ServiceProviderCreditPackage $package): array => [
                'value' => ServiceProviderCreditPackage::class.':'.$package->id,
                'label' => $package->name.' ('.$package->page_count.' pages)',
            ])
            ->values()
            ->all();

        return [
            ['group' => 'Subscription packages', 'options' => $subscriptionOptions],
            ['group' => 'Credit packages', 'options' => $creditOptions],
        ];
    }
}
