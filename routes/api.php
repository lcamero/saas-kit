<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->group(function () {
        // Route::get('/user', function (Request $request) {
        //     return $request->user();
        // })->middleware('auth:sanctum', 'ability:read');
    });
}
