<?php


declare(strict_types=1);


namespace App\Facility\Enums;

enum RoomStatus: string
{
    case Active = 'active';
    case Maintenance = 'maintenance';
    case Inactive = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Aktif',
            self::Maintenance => 'Perawatan',
            self::Inactive => 'Tidak Aktif',
        };
    }

    public function isReservable(): bool
    {
        return $this === self::Active;
    }
}
