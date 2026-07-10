<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use App\Models\Policy;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class PolicyController extends Controller
{
    public function termsOfService(): Response
    {
        return $this->show(config('constants.policy.type.terms_of_service'));
    }

    public function privacyPolicy(): Response
    {
        return $this->show(config('constants.policy.type.privacy_policy'));
    }

    public function aboutUs(): Response
    {
        return $this->show(config('constants.policy.type.about_us'));
    }

    private function show(string $type): Response
    {
        $policy = Policy::query()
            ->where('type', $type)
            ->published()
            ->firstOrFail();

        return Inertia::render('marketing/Policy/Show', [
            'canRegister' => Features::enabled(Features::registration()),
            'policy' => [
                'type' => $policy->type,
                'title' => $policy->title,
                'body' => $policy->body,
                'version' => $policy->version,
                'published_at' => $policy->published_at?->toIso8601String(),
            ],
        ]);
    }
}
