<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended($this->getDashboardRoute().'?verified=1');
        }

        $request->fulfill();

        return redirect()->intended($this->getDashboardRoute().'?verified=1');
    }

    protected function getDashboardRoute(): string
    {
        // Check if we're in a tenant context
        if (tenancy()->initialized) {
            return route('tenant.dashboard', absolute: false);
        }

        return route('dashboard', absolute: false);
    }
}
