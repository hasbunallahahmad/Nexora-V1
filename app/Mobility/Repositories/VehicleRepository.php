<?php

declare(strict_types=1);

namespace App\Mobility\Repositories;

use App\Mobility\Enums\VehicleStatus;
use App\Mobility\Models\Vehicle;
use App\Shared\DTO\DateRange;
use Illuminate\Support\Collection;

final class VehicleRepository
{
    public function findOrFail(int $id): Vehicle
    {
        return Vehicle::query()->findOrFail($id);
    }

    /**
     * Mengunci baris Vehicle sebagai mutex — wajib dipanggil di dalam
     * DB::transaction() sebelum cek konflik & tulis reservasi. Pola identik
     * dengan RoomRepository::lockForReservation() di Facility.
     */
    public function lockForReservation(int $vehicleId): Vehicle
    {
        return Vehicle::query()->whereKey($vehicleId)->lockForUpdate()->firstOrFail();
    }

    public function create(array $data): Vehicle
    {
        return Vehicle::create($data);
    }

    public function update(Vehicle $vehicle, array $data): Vehicle
    {
        $vehicle->update($data);

        return $vehicle->fresh();
    }

    public function availableInRange(DateRange $range, ?int $minCapacity = null): Collection
    {
        return Vehicle::query()
            ->where('status', VehicleStatus::Active)
            ->when($minCapacity, fn($q) => $q->where('capacity', '>=', $minCapacity))
            ->whereDoesntHave('occupyingReservations', function ($q) use ($range): void {
                $q->where('start_datetime', '<', $range->end)
                    ->where('end_datetime', '>', $range->start);
            })
            ->whereDoesntHave('maintenancePeriods', function ($q) use ($range): void {
                $q->where('start_datetime', '<', $range->end)
                    ->where('end_datetime', '>', $range->start);
            })
            ->get();
    }
}
