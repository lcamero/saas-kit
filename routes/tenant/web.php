<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use Stancl\Tenancy\Features\UserImpersonation;
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

    Route::get('/impersonate/{token}', function ($token) {
        return UserImpersonation::makeResponse($token);
    })->name('impersonate');

    Route::middleware(['auth:sanctum', 'verified'])->group(function () {
        Route::get('dashboard', function () {
            return view('tenant.dashboard');
        })->name('dashboard');

        Route::redirect('settings', '/settings/profile');

        // General settings
        Volt::route('settings/general', 'tenant.settings.general')
            ->can(\App\Enums\Tenant\Permission::ManageApplicationSettings)
            ->name('settings.general');

        // Personal preferences
        Volt::route('settings/profile', 'tenant.settings.profile')->name('settings.profile');
        Volt::route('settings/authentication', 'tenant.settings.authentication')->name('settings.authentication');
        Volt::route('settings/api-tokens', 'tenant.settings.api-tokens')->name('settings.api-tokens');

        Route::middleware('can:'.\App\Enums\Tenant\Permission::ManageApplicationUsers->value)->group(function () {
            Route::prefix('users')->name('users.')->group(function () {
                Volt::route('/', 'tenant.users.index')->name('index');
                Volt::route('create', 'tenant.users.create')->name('create');
                Volt::route('{userId}/edit', 'tenant.users.edit')->name('edit');
            });
        });
    });

    require __DIR__.'/auth.php';
});
