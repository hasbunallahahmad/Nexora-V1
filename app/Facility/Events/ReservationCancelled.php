<?php

declare(strict_types=1);

namespace App\Facility\Events;

use App\Facility\Models\RoomReservation;

final class ReservationCancelled
{
    public function __construct(
        public readonly RoomReservation $reservation,
    ) {}
}
