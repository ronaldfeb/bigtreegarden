<?php

namespace App\Http\Controllers\Provider;

use App\Enums\ServiceProviderCreditPurchaseStatus;
use App\Models\ServiceProvider;
use App\Models\ServiceProviderCreditPurchase;
use App\Models\ServiceProviderUser;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $serviceProvider = $this->currentProvider($request);
        $serviceProvider->loadCount(['services', 'specialities', 'socialMedia', 'images', 'personsOfInterest', 'backgrounds']);

        $pendingBankTransfers = ServiceProviderCreditPurchase::query()
            ->where('service_provider_id', $serviceProvider->id)
            ->where('status', ServiceProviderCreditPurchaseStatus::PendingReview)
            ->count();

        /** @var ServiceProviderUser $membership */
        $membership = $request->attributes->get('serviceProviderMembership');

        return Inertia::render('provider/Dashboard', [
            'serviceProvider' => $serviceProvider->only([
                'id', 'name', 'slug', 'status', 'logo_path', 'city', 'province', 'credits_remaining',
            ]),
            'counts' => [
                'services' => $serviceProvider->services_count,
                'specialities' => $serviceProvider->specialities_count,
                'social_media' => $serviceProvider->social_media_count,
                'images' => $serviceProvider->images_count,
                'memorials' => $serviceProvider->persons_of_interest_count,
                'backgrounds' => $serviceProvider->backgrounds_count,
                'pending_bank_transfers' => $pendingBankTransfers,
            ],
            'profileCompleteness' => $this->profileCompleteness($serviceProvider),
            'isOwner' => $membership->isOwner(),
        ]);
    }

    /**
     * Percentage (0-100) of optional profile fields that have been filled in.
     */
    private function profileCompleteness(ServiceProvider $serviceProvider): int
    {
        $fields = [
            'name',
            'description',
            'registration_number',
            'vat_number',
            'email',
            'phone',
            'website_url',
            'physical_address',
            'city',
            'province',
            'logo_path',
            'cover_image_path',
        ];

        $filled = collect($fields)
            ->filter(fn (string $field): bool => filled($serviceProvider->getAttribute($field)))
            ->count();

        return (int) round($filled / count($fields) * 100);
    }
}
