<?php

use App\Enums\Tenant\Role;
use App\Models\Tenant\User;

it('creates a seeded administrator with the CentralAdministrator role', function () {
    $user = User::where('email', config('tenancy.provision_admin_email'))->first();

    expect($user)->not->toBeNull()
        ->and($user->hasRole(Role::CentralAdministrator->value))->toBeTrue();
});
