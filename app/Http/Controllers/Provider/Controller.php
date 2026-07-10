<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller as BaseController;
use App\Models\ServiceProvider;
use App\Models\ServiceProviderUser;
use Illuminate\Http\Request;

abstract class Controller extends BaseController
{
    /**
     * Resolve the service provider that the authenticated user belongs to.
     *
     * The EnsureUserIsServiceProviderMember middleware guarantees the
     * membership attribute is present on every provider portal request.
     */
    protected function currentProvider(Request $request): ServiceProvider
    {
        /** @var ServiceProviderUser|null $membership */
        $membership = $request->attributes->get('serviceProviderMembership');

        if ($membership === null) {
            $membership = ServiceProviderUser::query()
                ->where('user_id', $request->user()->id)
                ->firstOrFail();
        }

        return $membership->serviceProvider;
    }
}
