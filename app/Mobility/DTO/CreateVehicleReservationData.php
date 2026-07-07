<?php

declare(strict_types=1);

namespace App\Mobility\DTO;

use Carbon\CarbonInterface;

final readonly class CreateVehicleReservationData
{
    public function __construct(
        public int $vehicleId,
        public ?int $agendaId,
        public int $requestedBy,
        public string $title,
        public ?string $destination,
        public ?string $purpose,
        public CarbonInterface $startDatetime,
        public CarbonInterface $endDatetime,
        public ?string $notes = null,
    ) {}
}
