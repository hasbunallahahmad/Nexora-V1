<?php

declare(strict_types=1);

namespace App\Mobility\Services;

use App\Mobility\Enums\VehicleStatus;
use App\Mobility\Models\Vehicle;
use App\Mobility\Repositories\VehicleRepository;
use App\Shared\DTO\DateRange;
use Illuminate\Support\Collection;

final class VehicleService
{
    public function __construct(
        private readonly VehicleRepository $vehicles,
    ) {}

    public function create(array $data): Vehicle
    {
        return $this->vehicles->create($data);
    }

    public function update(int $id, array $data): Vehicle
    {
        return $this->vehicles->update($this->vehicles->findOrFail($id), $data);
    }

    public function setStatus(int $id, VehicleStatus $status): Vehicle
    {
        return $this->vehicles->update($this->vehicles->findOrFail($id), ['status' => $status]);
    }

    public function listAvailable(DateRange $range, ?int $minCapacity = null): Collection
    {
        return $this->vehicles->availableInRange($range, $minCapacity);
    }
}
