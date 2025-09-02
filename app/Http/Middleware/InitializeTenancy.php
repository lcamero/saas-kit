<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InitializeTenancy
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $handler = config('tenancy.identification_handler');

        if (! $handler || ! class_exists($handler)) {
            throw new \Exception('Tenancy identification handler not configured or class does not exist.');
        }

        return app($handler)->handle($request, $next);
    }
}
