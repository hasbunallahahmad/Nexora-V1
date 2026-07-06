<?php

declare(strict_types=1);

namespace App\Facility\DTO;

use Carbon\CarbonInterface;

final readonly class CreateRoomReservationData
{
    public function __construct(
        public int $roomId,
        public ?int $agendaId,
        public int $requestedBy,
        public string $title,
        public ?string $purpose,
        public CarbonInterface $startDatetime,
        public CarbonInterface $endDatetime,
        public ?string $notes = null,
    ) {}
}
