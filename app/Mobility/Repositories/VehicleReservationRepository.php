<?php

declare(strict_types=1);

namespace App\Mobility\Repositories;

use App\Mobility\Models\VehicleReservation;
use App\Shared\Enums\ReservationStatus;
use Illuminate\Database\Eloquent\Builder;

final class VehicleReservationRepository
{
    public function findOrFail(int $id): VehicleReservation
    {
        return VehicleReservation::query()->findOrFail($id);
    }

    /**
     * Base query reservasi occupying (Submitted/Approved) untuk satu
     * kendaraan — diserahkan ke ConflictDetectionService.
     */
    public function occupyingQueryForVehicle(int $vehicleId): Builder
    {
        return VehicleReservation::query()
            ->where('vehicle_id', $vehicleId)
            ->whereIn(
                'status',
                array_map(fn($status) => $status->value, ReservationStatus::occupyingStatuses())
            );
    }

    public function create(array $data): VehicleReservation
    {
        return VehicleReservation::create($data);
    }

    public function updateStatus(VehicleReservation $reservation, array $attributes): VehicleReservation
    {
        $reservation->update($attributes);

        return $reservation->fresh();
    }
}
