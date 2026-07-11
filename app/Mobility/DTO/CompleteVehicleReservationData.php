<?php

declare(strict_types=1);

namespace App\Mobility\DTO;

use Carbon\CarbonInterface;

final readonly class CompleteVehicleReservationData
{
    public function __construct(
        public int $reservationId,
        public int $completedBy,
        public CarbonInterface $actualEndDatetime,
    ) {}
}
