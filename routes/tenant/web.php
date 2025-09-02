<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/
Route::middleware([
    'web',
    \App\Http\Middleware\InitializeTenancy::class,
    PreventAccessFromCentralDomains::class,
])->name('tenant.')->group(function () {
    Route::redirect('/', 'login');

    Route::middleware(['auth:sanctum', 'verified'])->group(function () {
        Route::get('dashboard', function () {
            return view('tenant.dashboard');
        })->name('dashboard');

        Route::redirect('settings', 'settings/profile');

        Volt::route('settings/profile', 'tenant.settings.profile')->name('settings.profile');
        Volt::route('settings/authentication', 'tenant.settings.authentication')->name('settings.authentication');
        Volt::route('settings/api-tokens', 'tenant.settings.api-tokens')->name('settings.api-tokens');
    });

    require __DIR__.'/auth.php';
});
