<?php

namespace App\Enums;

enum Role: string
{
    case SuperAdmin = 'superadmin';
    case Owner      = 'owner';
    case Admin      = 'admin';
    case Driver     = 'driver';

    public function label(): string
    {
        return match ($this) {
            Role::SuperAdmin => 'Super Admin',
            Role::Owner      => 'Owner',
            Role::Admin      => 'Admin',
            Role::Driver     => 'Driver',
        };
    }

    // Apakah role ini terikat ke 1 agent?
    public function isAgentBound(): bool
    {
        return $this === Role::Admin;
    }

    // Apakah bisa lihat semua data?
    public function canViewAll(): bool
    {
        return in_array($this, [Role::SuperAdmin, Role::Owner]);
    }
}
