<?php

declare(strict_types=1);

namespace App\Mobility\Repositories;

use App\Mobility\Models\VehicleMaintenancePeriod;
use Illuminate\Database\Eloquent\Builder;

final class VehicleMaintenanceRepository
{
    /**
     * Base query seluruh periode maintenance untuk satu kendaraan —
     * diserahkan ke ConflictDetectionService yang sama dengan yang
     * dipakai untuk cek overlap reservasi.
     */
    public function queryForVehicle(int $vehicleId): Builder
    {
        return VehicleMaintenancePeriod::query()->where('vehicle_id', $vehicleId);
    }

    public function create(array $data): VehicleMaintenancePeriod
    {
        return VehicleMaintenancePeriod::create($data);
    }
}
