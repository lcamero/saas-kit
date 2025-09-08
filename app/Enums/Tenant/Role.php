<?php

namespace App\Enums\Tenant;

enum Role: string
{
    case CentralAdministrator = 'central_administrator';
    case Administrator = 'administrator';

    public function getLabel(): string
    {
        return match ($this) {
            self::CentralAdministrator => __('general.central_administrator'),
            self::Administrator => __('general.administrator'),
        };
    }
}
