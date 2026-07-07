<?php

declare(strict_types=1);

namespace App\Mobility\Services;

use App\Mobility\Models\VehicleMaintenancePeriod;
use App\Mobility\Repositories\VehicleMaintenanceRepository;

final class VehicleMaintenanceService
{
    public function __construct(
        private readonly VehicleMaintenanceRepository $maintenance,
    ) {}

    public function schedule(array $data): VehicleMaintenancePeriod
    {
        return $this->maintenance->create($data);
    }
}
