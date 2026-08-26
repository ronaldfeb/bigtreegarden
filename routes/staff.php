<?php

use App\Http\Controllers\Staff\Commerce\PlatformBankDetailController;
use App\Http\Controllers\Staff\Commerce\ServiceProviderCreditPackageController;
use App\Http\Controllers\Staff\Commerce\ServiceProviderCreditPurchaseController;
use App\Http\Controllers\Staff\Commerce\SubscriptionController;
use App\Http\Controllers\Staff\Commerce\SubscriptionPackageController;
use App\Http\Controllers\Staff\Commerce\TransactionController;
use App\Http\Controllers\Staff\Content\AmbassadorController;
use App\Http\Controllers\Staff\Content\BlogCategoryController;
use App\Http\Controllers\Staff\Content\BlogController;
use App\Http\Controllers\Staff\Content\HelpCenterArticleController;
use App\Http\Controllers\Staff\Content\HelpCenterCategoryController;
use App\Http\Controllers\Staff\Content\HelpCenterTopicController;
use App\Http\Controllers\Staff\Content\MemorialPagePamphletBackgroundCollectionController;
use App\Http\Controllers\Staff\Content\MemorialPagePamphletBackgroundController;
use App\Http\Controllers\Staff\Content\PartnerController;
use App\Http\Controllers\Staff\Content\PolicyController;
use App\Http\Controllers\Staff\Content\TestimonialController;
use App\Http\Controllers\Staff\Crm\CrmContactController;
use App\Http\Controllers\Staff\Crm\CrmDashboardController;
use App\Http\Controllers\Staff\Crm\CrmFollowUpController;
use App\Http\Controllers\Staff\Crm\CrmInteractionController;
use App\Http\Controllers\Staff\Crm\CrmOrganisationController;
use App\Http\Controllers\Staff\Crm\LeadConversionController;
use App\Http\Controllers\Staff\DashboardController;
use App\Http\Controllers\Staff\Directory\MemorialPageMessageController;
use App\Http\Controllers\Staff\Directory\PersonOfInterestController;
use App\Http\Controllers\Staff\Directory\ServiceProviderController;
use App\Http\Controllers\Staff\Directory\UserController;
use App\Http\Controllers\Staff\Directory\VaultController;
use App\Http\Controllers\Staff\Marketing\MarketingAdvertController;
use App\Http\Controllers\Staff\Marketing\MarketingLeadController;
use App\Http\Controllers\Staff\Marketing\MarketingLeadNoteController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::resource('marketing/adverts', MarketingAdvertController::class)->names('marketing.adverts');
Route::resource('marketing/leads', MarketingLeadController::class)->names('marketing.leads');
Route::post('marketing/leads/{marketing_lead}/notes', [MarketingLeadNoteController::class, 'store'])
    ->name('marketing.leads.notes.store');
Route::delete('marketing/leads/{marketing_lead}/notes/{marketing_lead_note}', [MarketingLeadNoteController::class, 'destroy'])
    ->name('marketing.leads.notes.destroy');
Route::post('marketing/leads/{lead}/convert', [LeadConversionController::class, 'store'])
    ->name('marketing.leads.convert');

Route::get('crm', [CrmDashboardController::class, 'index'])->name('crm.dashboard');
Route::get('crm/follow-ups', [CrmFollowUpController::class, 'index'])->name('crm.follow-ups.index');
Route::post('crm/follow-ups', [CrmFollowUpController::class, 'store'])->name('crm.follow-ups.store');
Route::patch('crm/follow-ups/{followUp}', [CrmFollowUpController::class, 'update'])->name('crm.follow-ups.update');
Route::post('crm/follow-ups/{followUp}/complete', [CrmFollowUpController::class, 'complete'])->name('crm.follow-ups.complete');
Route::post('crm/follow-ups/{followUp}/cancel', [CrmFollowUpController::class, 'cancel'])->name('crm.follow-ups.cancel');
Route::post('crm/interactions', [CrmInteractionController::class, 'store'])->name('crm.interactions.store');
Route::delete('crm/interactions/{interaction}', [CrmInteractionController::class, 'destroy'])->name('crm.interactions.destroy');
Route::resource('crm/organisations', CrmOrganisationController::class)->names('crm.organisations');
Route::resource('crm/contacts', CrmContactController::class)->names('crm.contacts');

Route::resource('content/blogs', BlogController::class)->names('content.blogs');
Route::resource('content/blog-categories', BlogCategoryController::class)->names('content.blog-categories');
Route::resource('content/help-center-topics', HelpCenterTopicController::class)->names('content.help-center-topics');
Route::resource('content/help-center-categories', HelpCenterCategoryController::class)->names('content.help-center-categories');
Route::resource('content/help-center-articles', HelpCenterArticleController::class)->names('content.help-center-articles');
Route::resource('content/policies', PolicyController::class)->names('content.policies');
Route::resource('content/testimonials', TestimonialController::class)->names('content.testimonials');
Route::resource('content/partners', PartnerController::class)->names('content.partners');
Route::resource('content/ambassadors', AmbassadorController::class)->names('content.ambassadors');
Route::resource('content/pamphlet-background-collections', MemorialPagePamphletBackgroundCollectionController::class)
    ->names('content.pamphlet-background-collections');
Route::resource('content/pamphlet-backgrounds', MemorialPagePamphletBackgroundController::class)
    ->names('content.pamphlet-backgrounds');

Route::resource('commerce/subscription-packages', SubscriptionPackageController::class)->names('commerce.subscription-packages');
Route::resource('commerce/credit-packages', ServiceProviderCreditPackageController::class)
    ->parameters(['credit-packages' => 'creditPackage'])
    ->names('commerce.credit-packages');
Route::get('commerce/bank-details', [PlatformBankDetailController::class, 'edit'])->name('commerce.bank-details.edit');
Route::put('commerce/bank-details', [PlatformBankDetailController::class, 'update'])->name('commerce.bank-details.update');
Route::get('commerce/credit-purchases', [ServiceProviderCreditPurchaseController::class, 'index'])
    ->name('commerce.credit-purchases.index');
Route::get('commerce/credit-purchases/{creditPurchase}', [ServiceProviderCreditPurchaseController::class, 'show'])
    ->name('commerce.credit-purchases.show');
Route::post('commerce/credit-purchases/{creditPurchase}/release', [ServiceProviderCreditPurchaseController::class, 'release'])
    ->name('commerce.credit-purchases.release');
Route::post('commerce/credit-purchases/{creditPurchase}/reject', [ServiceProviderCreditPurchaseController::class, 'reject'])
    ->name('commerce.credit-purchases.reject');
Route::get('commerce/transactions', [TransactionController::class, 'index'])->name('commerce.transactions.index');
Route::get('commerce/transactions/{transaction}', [TransactionController::class, 'show'])->name('commerce.transactions.show');
Route::get('commerce/subscriptions', [SubscriptionController::class, 'index'])->name('commerce.subscriptions.index');

Route::get('directory/memorial-page-messages', [MemorialPageMessageController::class, 'index'])
    ->name('directory.memorial-page-messages.index');
Route::get('directory/vaults', [VaultController::class, 'index'])->name('directory.vaults.index');
Route::post('directory/service-providers/{service_provider}/approve', [ServiceProviderController::class, 'approve'])
    ->name('directory.service-providers.approve');
Route::post('directory/service-providers/{service_provider}/suspend', [ServiceProviderController::class, 'suspend'])
    ->name('directory.service-providers.suspend');
Route::resource('directory/service-providers', ServiceProviderController::class)->names('directory.service-providers');
Route::resource('directory/users', UserController::class)->names('directory.users');
Route::resource('directory/persons-of-interest', PersonOfInterestController::class)->names('directory.persons-of-interest');
