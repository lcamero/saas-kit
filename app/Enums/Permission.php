<?php

namespace App\Enums;

enum Permission: string
{
    case ManageApplicationSettings = 'manage application settings';
    case ManageApplicationUsers = 'manage application users';
    case ManageTenants = 'manage tenants';

    public function getLabel(): string
    {
        return match ($this) {
            self::ManageApplicationSettings => __('Manage Application Settings'),
            self::ManageApplicationUsers => __('Manage Application Users'),
            self::ManageTenants => __('Manage Tenants'),
        };
    }
}
