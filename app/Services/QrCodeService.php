<?php

namespace App\Services;

use App\Models\Pamphlet;

class QrCodeService
{
    /**
     * Build a renderable QR image URL.
     *
     * We rely on a public QR rendering endpoint to avoid adding
     * native QR dependencies in the first MVP iteration.
     */
    public function imageUrlForPamphlet(Pamphlet $pamphlet): string
    {
        $targetUrl = route('memorial.public.show', $pamphlet->public_slug);

        return sprintf(
            'https://api.qrserver.com/v1/create-qr-code/?size=320x320&data=%s',
            rawurlencode($targetUrl)
        );
    }
}
