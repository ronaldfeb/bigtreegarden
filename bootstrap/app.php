<?php

use App\Http\Middleware\EnsureUserIsServiceProviderMember;
use App\Http\Middleware\EnsureUserIsStaff;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Models\HelpCenterArticle;
use App\Models\HelpCenterTopic;
use App\Models\MemorialPagePamphlet;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            Route::bind('pamphlet', fn (string $value) => MemorialPagePamphlet::query()->findOrFail($value));

            Route::bind('help_center_topic', function (string $value): HelpCenterTopic {
                $query = HelpCenterTopic::query();

                if (Str::isUuid($value)) {
                    return $query->whereKey($value)->firstOrFail();
                }

                return $query->where('slug', $value)->firstOrFail();
            });

            Route::bind('help_center_article', function (string $value): HelpCenterArticle {
                $query = HelpCenterArticle::query();

                if (Str::isUuid($value)) {
                    return $query->whereKey($value)->firstOrFail();
                }

                return $query->where('slug', $value)->firstOrFail();
            });

            Route::middleware(['web', 'auth', EnsureUserIsStaff::class])
                ->prefix('staff')
                ->name('staff.')
                ->group(base_path('routes/staff.php'));

            Route::middleware(['web', 'auth', EnsureUserIsServiceProviderMember::class])
                ->prefix('provider')
                ->name('provider.')
                ->group(base_path('routes/provider.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);
        $middleware->validateCsrfTokens(except: [
            'payments/*/notify',
            'payments/subscriptions/*/notify',
            'payments/flowers/*/notify',
            'payments/provider-credits/*/notify',
        ]);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
