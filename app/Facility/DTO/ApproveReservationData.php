<?php

declare(strict_types=1);

namespace App\Facility\DTO;

final readonly class ApproveReservationData
{
    public function __construct(
        public int $reservationId,
        public int $approvedBy,
    ) {}
}
