<?php

use App\Enums\Tenant\Permission;
use App\Enums\Tenant\Role;
use App\Models\Tenant\User;
use Spatie\Permission\Models\Permission as PermissionModel;

beforeEach(function () {
    // By default, no permissions
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    $permission = PermissionModel::create(['name' => Permission::ManageApplicationSettings->value, 'guard_name' => 'tenant']);
    $permission = PermissionModel::create(['name' => Permission::ManageApplicationUsers->value, 'guard_name' => 'tenant']);
});

it('denies access to general settings without permission', function () {
    $this->get(route('tenant.settings.general'))
        ->assertForbidden();

    $this->user->givePermissionTo(Permission::ManageApplicationSettings->value);

    $this->get(route('tenant.settings.general'))
        ->assertOk();
});

it('denies access to general settings without admin role', function (Role $role) {
    $this->get(route('settings.general'))
        ->assertForbidden();

    $this->user->assignRole(Role::Administrator);

    $this->get(route('settings.general'))
        ->assertOk();
})->with([Role::Administrator, Role::CentralAdministrator]);

it('denies access to user management routes without permission', function () {
    // Index
    $this->get(route('tenant.users.index'))->assertForbidden();

    // Create
    $this->get(route('tenant.users.create'))->assertForbidden();

    // Edit (fake user id = 1)
    $this->get(route('tenant.users.edit', ['userId' => $this->user->id]))->assertForbidden();

    // Now grant permission
    $this->user->givePermissionTo(Permission::ManageApplicationUsers->value);

    $this->get(route('tenant.users.index'))->assertOk();
    $this->get(route('tenant.users.create'))->assertOk();
    $this->get(route('tenant.users.edit', ['userId' => $this->user->id]))->assertOk();
});

it('denies access to user management routes without admin role', function (Role $role) {
    // Index
    $this->get(route('users.index'))->assertForbidden();

    // Create
    $this->get(route('users.create'))->assertForbidden();

    $this->get(route('users.edit', ['userId' => $this->user->id]))->assertForbidden();

    // Now grant permission
    $this->user->assignRole(Role::Administrator);

    $this->get(route('users.index'))->assertOk();
    $this->get(route('users.create'))->assertOk();
    $this->get(route('users.edit', ['userId' => $this->user->id]))->assertOk();
})->with([Role::Administrator, Role::CentralAdministrator]);
