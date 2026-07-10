<?php

use App\Http\Controllers\Provider\DashboardController;
use App\Http\Controllers\Provider\ImageController;
use App\Http\Controllers\Provider\ProfileController;
use App\Http\Controllers\Provider\ServiceController;
use App\Http\Controllers\Provider\SocialMediaController;
use App\Http\Controllers\Provider\SpecialityController;
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
