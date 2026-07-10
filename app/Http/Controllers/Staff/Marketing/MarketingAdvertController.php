<?php

namespace App\Http\Controllers\Staff\Marketing;

use App\Http\Controllers\Staff\Controller;
use App\Http\Requests\Staff\Marketing\StoreMarketingAdvertRequest;
use App\Http\Requests\Staff\Marketing\UpdateMarketingAdvertRequest;
use App\Models\MarketingAdvert;
use App\Services\QrCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class MarketingAdvertController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('manage-marketing');

        return Inertia::render('staff/marketing/adverts/Index', [
            'adverts' => MarketingAdvert::query()
                ->with('staffUser.user')
                ->withCount('visits')
                ->latest()
                ->paginate(20),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('manage-marketing');

        return Inertia::render('staff/marketing/adverts/Create');
    }

    public function store(StoreMarketingAdvertRequest $request, QrCodeService $qrCodeService): RedirectResponse
    {
        Gate::authorize('manage-marketing');

        $validated = $request->validated();
        $code = $qrCodeService->generateUniqueAdvertCode();

        $advert = MarketingAdvert::query()->create([
            ...$validated,
            'staff_user_id' => $this->staffUser($request)->id,
            'code' => $code,
            'qr_code_path' => $qrCodeService->imageUrlForTarget($qrCodeService->trackedUrlForMarketingAdvert(
                new MarketingAdvert(['code' => $code])
            )),
        ]);

        return redirect()->route('staff.marketing.adverts.show', $advert);
    }

    public function show(MarketingAdvert $advert): Response
    {
        Gate::authorize('manage-marketing');

        $advert->load('staffUser.user');
        $uniqueVisits = $advert->visits()->distinct('visitor_hash')->count('visitor_hash');

        return Inertia::render('staff/marketing/adverts/Show', [
            'advert' => $advert,
            'analytics' => [
                'total_visits' => $advert->visits()->count(),
                'unique_visits' => $uniqueVisits,
            ],
        ]);
    }

    public function edit(MarketingAdvert $advert): Response
    {
        Gate::authorize('manage-marketing');

        return Inertia::render('staff/marketing/adverts/Edit', [
            'advert' => $advert,
        ]);
    }

    public function update(UpdateMarketingAdvertRequest $request, MarketingAdvert $advert): RedirectResponse
    {
        Gate::authorize('manage-marketing');

        $advert->update($request->validated());

        return redirect()->route('staff.marketing.adverts.show', $advert);
    }

    public function destroy(MarketingAdvert $advert): RedirectResponse
    {
        Gate::authorize('manage-marketing');

        $advert->delete();

        return redirect()->route('staff.marketing.adverts.index');
    }
}
