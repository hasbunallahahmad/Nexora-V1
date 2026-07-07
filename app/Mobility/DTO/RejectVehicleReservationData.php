<?php

declare(strict_types=1);

namespace App\Mobility\DTO;

final readonly class RejectVehicleReservationData
{
    public function __construct(
        public int $reservationId,
        public int $rejectedBy,
        public string $reason,
    ) {}
}
