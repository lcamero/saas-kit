<?php

namespace App\Enums\Tenant;

enum Permission: string
{
    case ManageApplicationSettings = 'manage application settings';
    case ManageApplicationUsers = 'manage application users';

    public function getLabel(): string
    {
        return match ($this) {
            self::ManageApplicationSettings => __(__('settings.manage_application_settings')),
            self::ManageApplicationUsers => __(__('user.manage_application_users')),
        };
    }
}
