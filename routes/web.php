<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Livewire\Volt\Volt;

foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->group(function () {
        Route::get('/', function () {
            $readme = File::get(base_path('README.md'));

            $readmeHtml = Str::markdown($readme);

            return view('welcome', [
                'readme' => $readmeHtml,
            ]);
        })->name('home');

        Route::middleware(['auth:sanctum', 'verified'])->group(function () {
            Route::get('dashboard', DashboardController::class)
                ->name('dashboard');

            Route::redirect('settings', 'settings/general');

            // General settings
            Volt::route('settings/general', 'settings.general')->name('settings.general');
    
            // Personal preferences
            Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
            Volt::route('settings/authentication', 'settings.authentication')->name('settings.authentication');
            Volt::route('settings/api-tokens', 'settings.api-tokens')->name('settings.api-tokens');

            Route::name('tenants.')->group(function () {
                Volt::route('tenants', 'tenants.index')->name('index');
                Volt::route('create', 'tenants.create')->name('create');
                Volt::route('{tenantId}/edit', 'tenants.edit')->name('edit');
            });
        });

        require __DIR__.'/auth.php';
    });
}
