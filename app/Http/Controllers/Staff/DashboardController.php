<?php

namespace App\Http\Controllers\Staff;

use App\Enums\TransactionStatus;
use App\Models\MarketingLead;
use App\Models\MemorialPage;
use App\Models\PersonOfInterest;
use App\Models\ServiceProvider;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('staff/Dashboard', [
            'counts' => [
                'users' => User::query()->count(),
                'persons_of_interest' => PersonOfInterest::query()->count(),
                'memorial_pages' => MemorialPage::query()->count(),
                'marketing_leads' => MarketingLead::query()->where('status', 'new')->count(),
                'transactions' => Transaction::query()->where('status', TransactionStatus::Complete)->count(),
                'service_providers' => ServiceProvider::query()->count(),
            ],
            'staffUser' => $this->staffUser($request)->load('user'),
        ]);
    }
}
