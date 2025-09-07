<?php

use App\Enums\Tenant\Role;
use App\Models\Tenant\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        $permissions = [
            //
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // create roles and assign existing permissions
        $roles = [
            Role::CentralAdministrator->value => [
                //
            ],
            Role::Administrator->value => [
                //
            ],
        ];

        foreach ($roles as $roleName => $permissionNames) {
            $role = \Spatie\Permission\Models\Role::create(['name' => $roleName]);
            $role->givePermissionTo($permissionNames);
        }

        // Seed your first administrator
        $admininistrator = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make(Str::random(16)),
            'email_verified_at' => now(),
        ]);

        $admininistrator->assignRole([Role::CentralAdministrator->value]);
    }
};
