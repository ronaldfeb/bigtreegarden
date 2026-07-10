<?php

namespace App\Http\Controllers\Staff\Commerce;

use App\Http\Controllers\Staff\Controller;
use App\Models\Transaction;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class TransactionController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('manage-commerce');

        return Inertia::render('staff/commerce/transactions/Index', [
            'transactions' => Transaction::query()
                ->with('user')
                ->latest()
                ->paginate(20),
        ]);
    }

    public function show(Transaction $transaction): Response
    {
        Gate::authorize('manage-commerce');

        $transaction->load(['user', 'payable']);

        return Inertia::render('staff/commerce/transactions/Show', ['transaction' => $transaction]);
    }
}
