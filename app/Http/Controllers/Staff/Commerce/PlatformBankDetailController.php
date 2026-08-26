<?php

namespace App\Http\Controllers\Staff\Commerce;

use App\Http\Controllers\Staff\Controller;
use App\Http\Requests\Staff\Commerce\UpdatePlatformBankDetailRequest;
use App\Models\PlatformBankDetail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class PlatformBankDetailController extends Controller
{
    public function edit(): Response
    {
        Gate::authorize('manage-commerce');

        $bankDetail = PlatformBankDetail::current() ?? new PlatformBankDetail([
            'bank_name' => '',
            'account_name' => '',
            'account_number' => '',
            'branch_code' => '',
            'reference_note' => '',
            'is_active' => true,
        ]);

        return Inertia::render('staff/commerce/bank-details/Edit', [
            'bankDetail' => $bankDetail->only([
                'id',
                'bank_name',
                'account_name',
                'account_number',
                'branch_code',
                'reference_note',
                'is_active',
            ]),
        ]);
    }

    public function update(UpdatePlatformBankDetailRequest $request): RedirectResponse
    {
        Gate::authorize('manage-commerce');

        $validated = $request->validated();
        $current = PlatformBankDetail::current();

        if ($current === null) {
            PlatformBankDetail::query()->create([
                ...$validated,
                'is_active' => true,
            ]);
        } else {
            $current->update($validated);
        }

        return back()->with('status', 'Bank details updated.');
    }
}
