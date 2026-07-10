<?php

namespace App\Http\Controllers\Staff\Commerce;

use App\Http\Controllers\Staff\Controller;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class SubscriptionController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('manage-commerce');

        $status = $request->query('status');

        $subscriptions = Subscription::query()
            ->with(['user', 'package'])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString()
            ->through(fn (Subscription $subscription): array => [
                'id' => $subscription->id,
                'user_name' => $subscription->user?->name,
                'user_email' => $subscription->user?->email,
                'package_name' => $subscription->package?->name,
                'status' => $subscription->status->value,
                'next_billing_at' => $subscription->next_billing_at?->toDateTimeString(),
                'activated_at' => $subscription->activated_at?->toDateTimeString(),
            ]);

        return Inertia::render('staff/commerce/subscriptions/Index', [
            'subscriptions' => $subscriptions,
            'filters' => [
                'status' => $status,
            ],
        ]);
    }
}
