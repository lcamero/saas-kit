<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\Tenant\User as TenantsUser;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialiteGoogleController extends Controller
{
    public function __invoke()
    {
        $state = json_decode(base64_decode(request('state')), true);

        $socialiteUser = Socialite::driver('google')->stateless()->user();

        $tenantId = $state['tenant'] ?? null;
        $tenant = null;
        if ($tenantId) {
            $tenant = Tenant::findOrFail($tenantId);
            tenancy()->initialize($tenant);
        }

        $model = $tenant ? TenantsUser::class : User::class;

        $user = $model::updateOrCreate(
            ['email' => $socialiteUser->email],
            [
                'name' => $socialiteUser->name,
                'email' => $socialiteUser->email,
                'google_id' => $socialiteUser->id,
                'avatar' => $socialiteUser->avatar,
                'email_verified_at' => now(),
            ]
        );

        if (tenancy()->initialized) {
            $redirectUrlParts = parse_url($state['redirect']);
            $port = isset($redirectUrlParts['port']) ? ":{$redirectUrlParts['port']}" : '';
            $tenantUrl = "{$redirectUrlParts['scheme']}://{$redirectUrlParts['host']}{$port}";

            URL::forceRootUrl($tenantUrl);

            $token = Str::random(60);

            Cache::put("magic-login-token.{$token}", $user->id, now()->addSeconds(5));

            $url = URL::temporarySignedRoute(
                'tenant.auth.magic-login',
                now()->addSeconds(5),
                ['user' => $user->id, 'token' => $token]
            );

            return redirect($url);
        }

        Auth::login($user);

        return redirect()->to(route('dashboard', absolute: false));
    }
}
