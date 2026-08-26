<?php

namespace App\Http\Controllers\Staff\Commerce;

use App\Enums\ServiceProviderCreditPurchaseStatus;
use App\Http\Controllers\Staff\Controller;
use App\Http\Requests\Staff\Commerce\RejectServiceProviderCreditPurchaseRequest;
use App\Models\ServiceProviderCreditPurchase;
use App\Services\ServiceProviderCreditService;
use App\Support\MediaStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ServiceProviderCreditPurchaseController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('manage-directory');

        $status = $request->string('status')->toString();

        $purchases = ServiceProviderCreditPurchase::query()
            ->with(['serviceProvider:id,name,slug', 'purchasedBy:id,name,email'])
            ->when(
                filled($status),
                fn ($query) => $query->where('status', $status),
                fn ($query) => $query->where('status', ServiceProviderCreditPurchaseStatus::PendingReview),
            )
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('staff/commerce/credit-purchases/Index', [
            'purchases' => $purchases,
            'filters' => [
                'status' => $status !== '' ? $status : ServiceProviderCreditPurchaseStatus::PendingReview->value,
            ],
            'statuses' => collect(ServiceProviderCreditPurchaseStatus::cases())->map(
                fn (ServiceProviderCreditPurchaseStatus $case): array => [
                    'value' => $case->value,
                    'label' => str_replace('_', ' ', ucfirst($case->value)),
                ],
            )->values()->all(),
        ]);
    }

    public function show(ServiceProviderCreditPurchase $creditPurchase): Response
    {
        Gate::authorize('manage-directory');

        $creditPurchase->load([
            'serviceProvider:id,name,slug,credits_remaining,email,phone',
            'purchasedBy:id,name,email',
            'reviewedByStaffUser.user:id,name',
            'package:id,name',
        ]);

        return Inertia::render('staff/commerce/credit-purchases/Show', [
            'purchase' => [
                ...$creditPurchase->toArray(),
                'proof_of_payment_url' => MediaStorage::url($creditPurchase->proof_of_payment_path),
            ],
        ]);
    }

    public function release(
        Request $request,
        ServiceProviderCreditPurchase $creditPurchase,
        ServiceProviderCreditService $creditService,
    ): RedirectResponse {
        Gate::authorize('manage-directory');

        $creditService->releasePurchase(
            $creditPurchase,
            $request->user(),
            $this->staffUser($request),
        );

        return back()->with('status', 'Credits released to the service provider.');
    }

    public function reject(
        RejectServiceProviderCreditPurchaseRequest $request,
        ServiceProviderCreditPurchase $creditPurchase,
        ServiceProviderCreditService $creditService,
    ): RedirectResponse {
        Gate::authorize('manage-directory');

        $creditService->rejectPurchase(
            $creditPurchase,
            $this->staffUser($request),
            $request->validated('review_note'),
        );

        return back()->with('status', 'Purchase rejected.');
    }
}
