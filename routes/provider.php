<?php

use App\Http\Controllers\Provider\BackgroundController;
use App\Http\Controllers\Provider\CreditController;
use App\Http\Controllers\Provider\DashboardController;
use App\Http\Controllers\Provider\ImageController;
use App\Http\Controllers\Provider\MemorialController;
use App\Http\Controllers\Provider\ProfileController;
use App\Http\Controllers\Provider\ServiceController;
use App\Http\Controllers\Provider\SocialMediaController;
use App\Http\Controllers\Provider\SpecialityController;
use App\Http\Controllers\Provider\TeamController;
use App\Http\Middleware\EnsureServiceProviderIsActive;
use App\Http\Middleware\EnsureServiceProviderOwner;
use Illuminate\Support\Facades\Route;

Route::get('/', DashboardController::class)->name('dashboard');

Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');

Route::get('services', [ServiceController::class, 'index'])->name('services.index');
Route::post('services', [ServiceController::class, 'store'])->name('services.store');
Route::patch('services/{service}', [ServiceController::class, 'update'])->name('services.update');
Route::delete('services/{service}', [ServiceController::class, 'destroy'])->name('services.destroy');

Route::get('specialities', [SpecialityController::class, 'index'])->name('specialities.index');
Route::post('specialities', [SpecialityController::class, 'store'])->name('specialities.store');
Route::patch('specialities/{speciality}', [SpecialityController::class, 'update'])->name('specialities.update');
Route::delete('specialities/{speciality}', [SpecialityController::class, 'destroy'])->name('specialities.destroy');

Route::get('social-media', [SocialMediaController::class, 'index'])->name('social-media.index');
Route::post('social-media', [SocialMediaController::class, 'store'])->name('social-media.store');
Route::patch('social-media/{socialMedia}', [SocialMediaController::class, 'update'])->name('social-media.update');
Route::delete('social-media/{socialMedia}', [SocialMediaController::class, 'destroy'])->name('social-media.destroy');

Route::get('images', [ImageController::class, 'index'])->name('images.index');
Route::post('images', [ImageController::class, 'store'])->name('images.store');
Route::delete('images/{image}', [ImageController::class, 'destroy'])->name('images.destroy');

Route::get('backgrounds', [BackgroundController::class, 'index'])->name('backgrounds.index');
Route::post('backgrounds', [BackgroundController::class, 'store'])->name('backgrounds.store');
Route::delete('backgrounds/{background}', [BackgroundController::class, 'destroy'])->name('backgrounds.destroy');

Route::middleware(EnsureServiceProviderOwner::class)->group(function () {
    Route::get('team', [TeamController::class, 'index'])->name('team.index');
    Route::post('team', [TeamController::class, 'store'])->name('team.store');
    Route::patch('team/{userId}', [TeamController::class, 'update'])->name('team.update');
    Route::delete('team/{userId}', [TeamController::class, 'destroy'])->name('team.destroy');
    Route::delete('team/invitations/{invitation}', [TeamController::class, 'destroyInvitation'])
        ->name('team.invitations.destroy');
});

Route::middleware([EnsureServiceProviderOwner::class, EnsureServiceProviderIsActive::class])->group(function () {
    Route::get('credits', [CreditController::class, 'index'])->name('credits.index');
    Route::post('credits', [CreditController::class, 'store'])->name('credits.store');
    Route::get('credits/{purchase}/checkout', [CreditController::class, 'checkout'])->name('credits.checkout');
    Route::get('credits/{purchase}/bank-transfer', [CreditController::class, 'bankTransfer'])->name('credits.bank-transfer');
    Route::post('credits/{purchase}/proof', [CreditController::class, 'uploadProof'])->name('credits.proof');
    Route::get('credits/{purchase}/return', [CreditController::class, 'handleReturn'])->name('credits.return');
    Route::get('credits/{purchase}/cancel', [CreditController::class, 'handleCancel'])->name('credits.cancel');
});

Route::middleware(EnsureServiceProviderIsActive::class)->group(function () {
    Route::get('memorials', [MemorialController::class, 'index'])->name('memorials.index');
    Route::get('memorials/create', [MemorialController::class, 'create'])->name('memorials.create');
    Route::post('memorials', [MemorialController::class, 'store'])->name('memorials.store');
    Route::get('memorials/{pamphlet}', [MemorialController::class, 'show'])->name('memorials.show');
    Route::delete('memorials/{pamphlet}', [MemorialController::class, 'destroy'])->name('memorials.destroy');
});
