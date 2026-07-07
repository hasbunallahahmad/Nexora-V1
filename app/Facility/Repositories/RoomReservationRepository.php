<?php

declare(strict_types=1);

namespace App\Facility\Repositories;

use App\Shared\Enums\ReservationStatus;
use App\Facility\Models\RoomReservation;
use Illuminate\Database\Eloquent\Builder;

final class RoomReservationRepository
{
    public function findOrFail(int $id): RoomReservation
    {
        return RoomReservation::query()->findOrFail($id);
    }

    /**
     * Base query reservasi yang dianggap "menempati" jadwal
     * (Submitted/Approved) untuk satu ruangan — siap diserahkan ke
     * ConflictDetectionService.
     */
    public function occupyingQueryForRoom(int $roomId): Builder
    {
        return RoomReservation::query()
            ->where('room_id', $roomId)
            ->whereIn(
                'status',
                array_map(fn($status) => $status->value, ReservationStatus::occupyingStatuses())
            );
    }

    public function create(array $data): RoomReservation
    {
        return RoomReservation::create($data);
    }

    public function updateStatus(RoomReservation $reservation, array $attributes): RoomReservation
    {
        $reservation->update($attributes);

        return $reservation->fresh();
    }
}
