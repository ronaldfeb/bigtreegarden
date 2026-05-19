<?php

use App\Http\Controllers\AmbassadorController;
use App\Http\Controllers\Marketing\LandingController;
use App\Http\Controllers\MemorialPageController;
use App\Http\Controllers\PamphletController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\User\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', LandingController::class)->name('home');
Route::get('/ambassadors/{ambassador}', [AmbassadorController::class, 'show'])->name('ambassadors.show');

Route::get('/memorial/{slug}', [MemorialPageController::class, 'show'])->name('memorial.public.show');
Route::post('/payments/{pamphlet}/notify', [PaymentController::class, 'handleNotify'])->name('payments.notify');
Route::get('/pamphlets/create', [PamphletController::class, 'create'])->name('pamphlets.create');
Route::post('/pamphlets', [PamphletController::class, 'store'])->name('pamphlets.store');
Route::get('/pamphlets/{pamphlet}', [PamphletController::class, 'show'])->name('pamphlets.show');
Route::get('/pamphlets/{pamphlet}/continue', [PaymentController::class, 'continueToCheckout'])->name('pamphlets.continue');

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::get('/payments/{pamphlet}/checkout', [PaymentController::class, 'checkout'])->name('payments.checkout');
    Route::get('/payments/{pamphlet}/return', [PaymentController::class, 'handleReturn'])->name('payments.return');
    Route::get('/payments/{pamphlet}/cancel', [PaymentController::class, 'handleCancel'])->name('payments.cancel');

    Route::get('/pamphlets/{pamphlet}/memorial/edit', [MemorialPageController::class, 'edit'])->name('memorial.edit');
    Route::patch('/pamphlets/{pamphlet}/memorial', [MemorialPageController::class, 'update'])->name('memorial.update');
    Route::get('/pamphlets/{pamphlet}/print', [PamphletController::class, 'print'])->name('pamphlets.print');
});

require __DIR__.'/settings.php';
