<?php

declare(strict_types=1);

namespace App\Mobility\Enums;

enum VehicleStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Aktif',
            self::Inactive => 'Non-aktif',
        };
    }

    public function isReservable(): bool
    {
        return $this === self::Active;
    }
}
