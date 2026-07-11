<?php

declare(strict_types=1);

namespace App\Mobility\Events;

use App\Mobility\Models\VehicleReservation;

final class VehicleReservationCompleted
{
    public function __construct(
        public readonly VehicleReservation $reservation,
    ) {}
}
