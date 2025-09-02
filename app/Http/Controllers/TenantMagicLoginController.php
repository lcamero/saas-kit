<?php

namespace App\Http\Controllers;

use App\Models\Tenant\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class TenantMagicLoginController extends Controller
{
    public function __invoke(Request $request, User $user, string $token)
    {
        $cachedUserId = Cache::pull("magic-login-token.{$token}");
        abort_if(! $cachedUserId || $cachedUserId != $user->id, 401);

        Auth::guard('tenant')->login($user);

        $request->session()->regenerate();

        return redirect()->route('tenant.dashboard');
    }
}
