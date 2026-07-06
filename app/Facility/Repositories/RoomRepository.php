<?php

declare(strict_types=1);

namespace App\Facility\Repositories;

use App\Facility\Enums\RoomStatus;
use App\Facility\Models\Room;
use App\Shared\DTO\DateRange;
use Illuminate\Support\Collection;

final class RoomRepository
{
    public function findOrFail(int $id): Room
    {
        return Room::query()->findOrFail($id);
    }

    public function findBySlugOrFail(string $slug): Room
    {
        return Room::query()->where('slug', $slug)->firstOrFail();
    }

    public function lockForReservation(int $roomId): Room
    {
        return Room::query()->whereKey($roomId)->lockForUpdate()->firstOrFail();
    }

    public function create(array $data): Room
    {
        return Room::create($data);
    }

    public function update(Room $room, array $data): Room
    {
        $room->update($data);

        return $room->fresh();
    }

    public function availableInRange(DateRange $range, ?int $minCapacity = null): Collection
    {
        return Room::query()
            ->where('status', RoomStatus::Active)
            ->when($minCapacity, fn($q) => $q->where('capacity', '>=', $minCapacity))
            ->whereDoesntHave('occupyingReservations', function ($q) use ($range): void {
                $q->where('start_datetime', '<', $range->end)
                    ->where('end_datetime', '>', $range->start);
            })
            ->get();
    }
}
