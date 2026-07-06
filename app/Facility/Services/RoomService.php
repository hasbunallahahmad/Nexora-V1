<?php

declare(strict_types=1);

namespace App\Facility\Services;

use App\Facility\Enums\RoomStatus;
use App\Facility\Repositories\RoomRepository;
use App\Models\Facility\Models\Room;
use App\Shared\DTO\DateRange;
use Illuminate\Support\Collection;

final class RoomService
{
    public function __construct(
        private readonly RoomRepository $rooms,
    ) {}

    public function create(array $data): Room
    {
        return $this->rooms->create($data);
    }

    public function update(int $id, array $data): Room
    {
        return $this->rooms->update($this->rooms->findOrFail($id), $data);
    }

    public function setStatus(int $id, RoomStatus $status): Room
    {
        return $this->rooms->update($this->rooms->findOrFail($id), ['status' => $status]);
    }

    public function listAvailable(DateRange $range, ?int $minCapacity = null): Collection
    {
        return $this->rooms->availableInRange($range, $minCapacity);
    }
}
