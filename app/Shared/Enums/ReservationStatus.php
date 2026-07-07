<?php

declare(strict_types=1);

namespace App\Shared\Enums;

enum ReservationStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Submitted => 'Diajukan',
            self::Approved => 'Disetujui',
            self::Rejected => 'Ditolak',
            self::Cancelled => 'Dibatalkan',
            self::Completed => 'Selesai',
        };
    }

    public static function occupyingStatuses(): array
    {
        return [self::Submitted, self::Approved];
    }

    public function isOccupying(): bool
    {
        return in_array($this, self::occupyingStatuses(), true);
    }

    public function canTransitionTo(self $target): bool
    {
        return match ($this) {
            self::Draft => in_array($target, [self::Submitted, self::Cancelled], true),
            self::Submitted => in_array($target, [self::Approved, self::Rejected, self::Cancelled], true),
            self::Approved => in_array($target, [self::Cancelled, self::Completed], true),
            self::Rejected, self::Cancelled, self::Completed => false,
        };
    }
}
