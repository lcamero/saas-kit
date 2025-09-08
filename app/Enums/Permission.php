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
            self::ManageApplicationSettings => __('settings.manage_application_settings'),
            self::ManageApplicationUsers => __('settings.manage_application_users'),
            self::ManageTenants => __('settings.manage_tenants'),
        };
    }
}
