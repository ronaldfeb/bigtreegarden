<?php

namespace App\Services;

use App\Models\MarketingAdvert;
use App\Models\MemorialPagePamphlet;
use Illuminate\Support\Str;

class QrCodeService
{
    public function imageUrlForPamphlet(MemorialPagePamphlet $pamphlet): string
    {
        $targetUrl = route('memorial.public.show', $pamphlet->memorialPage?->public_slug);

        return $this->imageUrlForTarget($targetUrl);
    }

    public function imageUrlForMarketingAdvert(MarketingAdvert $advert): string
    {
        return $this->imageUrlForTarget($this->trackedUrlForMarketingAdvert($advert));
    }

    public function trackedUrlForMarketingAdvert(MarketingAdvert $advert): string
    {
        return url('/a/'.$advert->code);
    }

    public function imageUrlForTarget(string $targetUrl): string
    {
        return sprintf(
            'https://api.qrserver.com/v1/create-qr-code/?size=320x320&data=%s',
            rawurlencode($targetUrl)
        );
    }

    public function generateUniqueAdvertCode(): string
    {
        do {
            $code = Str::lower(Str::random(8));
        } while (MarketingAdvert::query()->where('code', $code)->exists());

        return $code;
    }
}
