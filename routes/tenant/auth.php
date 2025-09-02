<?php

use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\SocialiteController;
use App\Http\Controllers\TenantMagicLoginController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::middleware(['guest'])->group(function () {
    Volt::route('login', 'tenant.auth.login')
        ->name('login');

    Volt::route('register', 'tenant.auth.register')
        ->name('register');

    Volt::route('forgot-password', 'tenant.auth.forgot-password')
        ->name('password.request');

    Volt::route('reset-password/{token}', 'tenant.auth.reset-password')
        ->name('password.reset');

    // Socialite support
    Route::get('/auth/{provider}/redirect', SocialiteController::class)
        ->middleware(['universal'])
        ->name('socialite.redirect');
});

// Put outside guest so a second attempt while logged in throws an invalid response
Route::get('auth/magic-login/{user}/{token}', TenantMagicLoginController::class)
    ->middleware(['signed'])
    ->name('auth.magic-login');

Route::middleware('auth:sanctum')->group(function () {
    Volt::route('verify-email', 'tenant.auth.verify-email')
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1', 'universal'])
        ->name('verification.verify');

    Volt::route('confirm-password', 'tenant.auth.confirm-password')
        ->name('password.confirm');
});

Route::post('logout', App\Livewire\Actions\TenantsLogout::class)
    ->name('logout');
