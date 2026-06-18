<?php

namespace App\Services;

use App\Models\Pamphlet;
use App\Models\Payment;

class PamphletPaymentFulfillmentService
{
    public function __construct(private QrCodeService $qrCodeService) {}

    /**
     * @param  array<string, mixed>  $rawPayload
     */
    public function fulfill(Pamphlet $pamphlet, Payment $payment, array $rawPayload = [], ?string $providerPaymentId = null): void
    {
        if (in_array($pamphlet->status, ['paid', 'published'], true)) {
            return;
        }

        $payment->update([
            'provider_payment_id' => $providerPaymentId,
            'status' => 'paid',
            'paid_at' => now(),
            'raw_payload' => $rawPayload,
        ]);

        $pamphlet->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $targetUrl = route('memorial.public.show', $pamphlet->public_slug);

        $pamphlet->pamphletQrCode()->updateOrCreate([], [
            'target_url' => $targetUrl,
            'image_path' => $this->qrCodeService->imageUrlForPamphlet($pamphlet),
            'generated_at' => now(),
        ]);
    }
}
