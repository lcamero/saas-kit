<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant API Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant api routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'api',
    'auth:sanctum',
    \App\Http\Middleware\InitializeTenancy::class,
    PreventAccessFromCentralDomains::class,
])->prefix('api')->group(function () {
    // Route::get('/user', function (Request $request) {
    //     return $request->user();
    // })->middleware(['ability:read']);
});
