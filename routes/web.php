<?php

use App\Http\Controllers\AmbassadorController;
use App\Http\Controllers\FlowerPaymentController;
use App\Http\Controllers\Marketing\BlogController;
use App\Http\Controllers\Marketing\HelpCenterController;
use App\Http\Controllers\Marketing\LandingController;
use App\Http\Controllers\Marketing\MarketingAdvertRedirectController;
use App\Http\Controllers\Marketing\PartnerDirectoryController;
use App\Http\Controllers\Marketing\PolicyController;
use App\Http\Controllers\Marketing\PricingController;
use App\Http\Controllers\Marketing\ServiceProviderDirectoryController;
use App\Http\Controllers\MemorialPageController;
use App\Http\Controllers\MemorialPageLiveController;
use App\Http\Controllers\MemorialPageMessageModerationController;
use App\Http\Controllers\PamphletController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\Vault\VaultAccessController;
use App\Http\Controllers\Vault\VaultBeneficiaryController;
use App\Http\Controllers\Vault\VaultController;
use App\Http\Controllers\Vault\VaultMediaController;
use App\Http\Controllers\Vault\VaultPostController;
use App\Http\Middleware\EnsureUserHasActiveSubscription;
use Illuminate\Support\Facades\Route;

Route::get('/', LandingController::class)->name('home');
Route::get('/pricing', [PricingController::class, 'index'])->name('pricing');
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{blog:slug}', [BlogController::class, 'show'])->name('blog.show');
Route::get('/help', [HelpCenterController::class, 'index'])->name('help.index');
Route::get('/help/{helpCenterTopic:slug}', [HelpCenterController::class, 'topic'])->name('help.topic');
Route::get('/help/{helpCenterTopic:slug}/{helpCenterArticle:slug}', [HelpCenterController::class, 'article'])->name('help.article');
Route::get('/terms-of-service', [PolicyController::class, 'termsOfService'])->name('policies.terms');
Route::get('/privacy-policy', [PolicyController::class, 'privacyPolicy'])->name('policies.privacy');
Route::get('/about-us', [PolicyController::class, 'aboutUs'])->name('policies.about');
Route::get('/partners', [PartnerDirectoryController::class, 'index'])->name('partners.index');
Route::get('/providers', [ServiceProviderDirectoryController::class, 'index'])->name('providers.index');
Route::get('/providers/{serviceProvider:slug}', [ServiceProviderDirectoryController::class, 'show'])->name('providers.show');
Route::get('/a/{code}', MarketingAdvertRedirectController::class)->name('marketing.adverts.redirect');
Route::get('/ambassadors/{ambassador}', [AmbassadorController::class, 'show'])->name('ambassadors.show');

Route::get('/memorials', [MemorialPageController::class, 'find'])->name('memorial.find');
Route::get('/memorial/{slug}', [MemorialPageController::class, 'show'])->name('memorial.public.show');
Route::get('/memorial/{slug}/live', [MemorialPageLiveController::class, 'show'])->name('memorial.live.show');
Route::get('/memorial/{slug}/live/messages', [MemorialPageLiveController::class, 'messages'])->name('memorial.live.messages');
Route::post('/payments/{pamphlet}/notify', [PaymentController::class, 'handleNotify'])->name('payments.notify');
Route::post('/payments/subscriptions/{subscription}/notify', [SubscriptionController::class, 'handleNotify'])->name('subscriptions.notify');
Route::post('/payments/flowers/{message}/notify', [FlowerPaymentController::class, 'handleNotify'])->name('flowers.notify');

Route::get('/vault/access', [VaultAccessController::class, 'create'])->name('vault.access.create');
Route::post('/vault/access', [VaultAccessController::class, 'store'])->name('vault.access.store');
Route::get('/vault/access/view', [VaultAccessController::class, 'show'])->name('vault.access.show');
Route::get('/pamphlets/create', [PamphletController::class, 'create'])->name('pamphlets.create');
Route::post('/pamphlets', [PamphletController::class, 'store'])->name('pamphlets.store');
Route::get('/pamphlets/{pamphlet}/edit', [PamphletController::class, 'edit'])->name('pamphlets.edit');
Route::put('/pamphlets/{pamphlet}', [PamphletController::class, 'update'])->name('pamphlets.update');
Route::get('/pamphlets/{pamphlet}', [PamphletController::class, 'show'])->name('pamphlets.show');
Route::get('/pamphlets/{pamphlet}/continue', [PaymentController::class, 'continueToCheckout'])->name('pamphlets.continue');

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::post('/memorial/{slug}/flowers', [MemorialPageLiveController::class, 'storeFlower'])->name('memorial.flowers.store');
    Route::post('/memorial/{slug}/live/messages', [MemorialPageLiveController::class, 'store'])->name('memorial.live.store');
    Route::get('/payments/flowers/{message}/checkout', [FlowerPaymentController::class, 'checkout'])->name('flowers.checkout');
    Route::get('/payments/flowers/{message}/return', [FlowerPaymentController::class, 'handleReturn'])->name('flowers.return');
    Route::get('/payments/flowers/{message}/cancelled', [FlowerPaymentController::class, 'handleCancelled'])->name('flowers.cancelled');

    Route::post('/messages/{message}/approve', [MemorialPageMessageModerationController::class, 'approve'])->name('messages.approve');
    Route::post('/messages/{message}/reject', [MemorialPageMessageModerationController::class, 'reject'])->name('messages.reject');

    Route::get('/payments/{pamphlet}/checkout', [PaymentController::class, 'checkout'])->name('payments.checkout');
    Route::get('/payments/{pamphlet}/return', [PaymentController::class, 'handleReturn'])->name('payments.return');
    Route::get('/payments/{pamphlet}/cancel', [PaymentController::class, 'handleCancel'])->name('payments.cancel');

    Route::get('/subscriptions/manage', [SubscriptionController::class, 'show'])->name('subscriptions.show');
    Route::post('/subscriptions/{package}', [SubscriptionController::class, 'store'])->name('subscriptions.store');
    Route::get('/subscriptions/{subscription}/checkout', [SubscriptionController::class, 'checkout'])->name('subscriptions.checkout');
    Route::get('/subscriptions/{subscription}/return', [SubscriptionController::class, 'handleReturn'])->name('subscriptions.return');
    Route::get('/subscriptions/{subscription}/cancelled', [SubscriptionController::class, 'handleCancelled'])->name('subscriptions.cancelled');
    Route::delete('/subscriptions/{subscription}', [SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');

    Route::get('/pamphlets/{pamphlet}/memorial/edit', [MemorialPageController::class, 'edit'])->name('memorial.edit');
    Route::patch('/pamphlets/{pamphlet}/memorial', [MemorialPageController::class, 'update'])->name('memorial.update');
    Route::get('/pamphlets/{pamphlet}/print', [PamphletController::class, 'print'])->name('pamphlets.print');
});

Route::middleware(['auth', EnsureUserHasActiveSubscription::class])
    ->prefix('vault')
    ->name('vault.')
    ->group(function () {
        Route::get('/', [VaultController::class, 'index'])->name('index');
        Route::post('/', [VaultController::class, 'store'])->name('store');
        Route::get('/{vault}', [VaultController::class, 'show'])->name('show');
        Route::post('/{vault}/release', [VaultController::class, 'release'])->name('release');

        Route::post('/{vault}/beneficiaries', [VaultBeneficiaryController::class, 'store'])->name('beneficiaries.store');
        Route::patch('/{vault}/beneficiaries/{beneficiary}', [VaultBeneficiaryController::class, 'update'])->name('beneficiaries.update');
        Route::delete('/{vault}/beneficiaries/{beneficiary}', [VaultBeneficiaryController::class, 'destroy'])->name('beneficiaries.destroy');

        Route::post('/{vault}/media', [VaultMediaController::class, 'store'])->name('media.store');
        Route::delete('/{vault}/media/{media}', [VaultMediaController::class, 'destroy'])->name('media.destroy');

        Route::post('/{vault}/posts', [VaultPostController::class, 'store'])->name('posts.store');
        Route::patch('/{vault}/posts/{post}', [VaultPostController::class, 'update'])->name('posts.update');
        Route::delete('/{vault}/posts/{post}', [VaultPostController::class, 'destroy'])->name('posts.destroy');
    });

require __DIR__.'/settings.php';
