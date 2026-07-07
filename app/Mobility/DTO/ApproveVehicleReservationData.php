<?php

declare(strict_types=1);

namespace App\Mobility\DTO;

final readonly class ApproveVehicleReservationData
{
    public function __construct(
        public int $reservationId,
        public int $approvedBy,
    ) {}
}
