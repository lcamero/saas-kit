<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class RedirectIfEmailNotVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  string|null  $redirectToRoute
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse|null
     */
    public function handle(Request $request, Closure $next, $redirectToRoute = null)
    {
        if (! $request->user() ||
            ($request->user() instanceof MustVerifyEmail &&
            ! $request->user()->hasVerifiedEmail())) {

            $routeName = tenant() ? 'tenant.verification.notice' : 'verification.notice';

            return $request->expectsJson()
                    ? abort(403, 'Your email address is not verified.')
                    : Redirect::route($routeName);
        }

        return $next($request);
    }
}
