<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetApplicationSettings
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        config([
            'app.name' => tenant()
                ? app(\App\Settings\Tenant\GeneralSettings::class)->application_name
                : app(\App\Settings\GeneralSettings::class)->application_name,
        ]);

        return $next($request);
    }
}
