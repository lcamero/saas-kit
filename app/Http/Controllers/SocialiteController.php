<?php

namespace App\Http\Controllers;

use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    public function __invoke(string $provider)
    {
        $tenant = tenant();

        return Socialite::driver($provider)
            ->stateless()
            ->with(['state' => base64_encode(json_encode([
                'tenant' => $tenant?->id,
                'redirect' => $tenant?->url,
            ]))])
            ->redirect();
    }
}
