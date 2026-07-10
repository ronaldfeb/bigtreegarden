<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\MarketingAdvert;
use App\Models\MarketingAdvertVisit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MarketingAdvertRedirectController extends Controller
{
    public function __invoke(Request $request, string $code): RedirectResponse
    {
        $advert = MarketingAdvert::query()
            ->where('code', $code)
            ->active()
            ->firstOrFail();

        $visitorHash = hash('sha256', ($request->ip() ?? '').($request->userAgent() ?? ''));

        MarketingAdvertVisit::query()->create([
            'marketing_advert_id' => $advert->id,
            'visitor_hash' => $visitorHash,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referrer' => $request->headers->get('referer'),
            'visited_at' => now(),
        ]);

        return redirect()->away($this->buildDestinationUrl($advert));
    }

    private function buildDestinationUrl(MarketingAdvert $advert): string
    {
        $params = array_filter([
            'utm_source' => $advert->utm_source,
            'utm_medium' => $advert->utm_medium,
            'utm_campaign' => $advert->utm_campaign,
            'utm_content' => $advert->utm_content,
        ], fn (?string $value): bool => $value !== null && $value !== '');

        if ($params === []) {
            return $advert->destination_url;
        }

        $separator = str_contains($advert->destination_url, '?') ? '&' : '?';

        return $advert->destination_url.$separator.http_build_query($params);
    }
}
