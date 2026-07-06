<?php

declare(strict_types=1);

namespace App\Facility\DTO;

final readonly class RejectReservationData
{
    public function __construct(
        public int $reservationId,
        public int $rejectedBy,
        public string $reason,
    ) {}
}
