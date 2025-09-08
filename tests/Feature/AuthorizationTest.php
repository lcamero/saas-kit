<?php

use App\Enums\Permission;
use App\Enums\Role;
use App\Models\User;
use Spatie\Permission\Models\Permission as PermissionModel;

beforeEach(function () {
    // By default, no permissions
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    $permission = PermissionModel::create(['name' => Permission::ManageApplicationSettings->value]);
    $permission = PermissionModel::create(['name' => Permission::ManageApplicationUsers->value]);
    $permission = PermissionModel::create(['name' => Permission::ManageTenants->value]);
});

it('denies access to general settings without permission', function () {
    $this->get(route('settings.general'))
        ->assertForbidden();

    $this->user->givePermissionTo(Permission::ManageApplicationSettings->value);

    $this->get(route('settings.general'))
        ->assertOk();
});

it('denies access to general settings without admin role', function () {
    $this->get(route('settings.general'))
        ->assertForbidden();

    $this->user->assignRole(Role::Administrator);

    $this->get(route('settings.general'))
        ->assertOk();
});

it('denies access to user management routes without permission', function () {
    // Index
    $this->get(route('users.index'))->assertForbidden();

    // Create
    $this->get(route('users.create'))->assertForbidden();

    $this->get(route('users.edit', ['userId' => $this->user->id]))->assertForbidden();

    // Now grant permission
    $this->user->givePermissionTo(Permission::ManageApplicationUsers->value);

    $this->get(route('users.index'))->assertOk();
    $this->get(route('users.create'))->assertOk();
    $this->get(route('users.edit', ['userId' => $this->user->id]))->assertOk();
});

it('denies access to user management routes without admin role', function () {
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
});

it('denies access to tenant routes without permission', function () {
    // Index
    $this->get(route('tenants.index'))->assertForbidden();

    // Create
    $this->get(route('tenants.create'))->assertForbidden();

    // Edit (fake tenant id = 1)
    $this->get(route('tenants.edit', ['tenantId' => 1]))->assertForbidden();

    // Now grant permission
    $this->user->givePermissionTo(Permission::ManageTenants->value);

    $this->get(route('tenants.index'))->assertOk();
    $this->get(route('tenants.create'))->assertOk();
    $this->get(route('tenants.edit', ['tenantId' => 1]))->assertNotFound();
});

it('denies access to tenant routes without admin role', function () {
    // Index
    $this->get(route('tenants.index'))->assertForbidden();

    // Create
    $this->get(route('tenants.create'))->assertForbidden();

    // Edit (fake tenant id = 1)
    $this->get(route('tenants.edit', ['tenantId' => 1]))->assertForbidden();

    // Now grant permission
    $this->user->assignRole(Role::Administrator);

    $this->get(route('tenants.index'))->assertOk();
    $this->get(route('tenants.create'))->assertOk();
    $this->get(route('tenants.edit', ['tenantId' => 1]))->assertNotFound();
});
