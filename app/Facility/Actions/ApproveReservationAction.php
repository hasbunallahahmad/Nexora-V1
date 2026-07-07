<?php

declare(strict_types=1);

namespace App\Facility\Actions;

use App\Facility\DTO\ApproveReservationData;
use App\Shared\Enums\ReservationStatus;
use App\Facility\Events\ReservationApproved;
use App\Facility\Exceptions\InvalidReservationTransitionException;
use App\Facility\Exceptions\ReservationConflictException;
use App\Facility\Models\RoomReservation;
use App\Facility\Repositories\RoomRepository;
use App\Facility\Repositories\RoomReservationRepository;
use App\Shared\DTO\DateRange;
use App\Shared\Services\ConflictDetectionService;
use Illuminate\Support\Facades\DB;

final class ApproveReservationAction
{
    public function __construct(
        private readonly RoomRepository $rooms,
        private readonly RoomReservationRepository $reservations,
        private readonly ConflictDetectionService $conflicts,
    ) {}

    public function execute(ApproveReservationData $data): RoomReservation
    {
        $reservation = DB::transaction(function () use ($data): RoomReservation {
            $reservation = $this->reservations->findOrFail($data->reservationId);

            if (! $reservation->status->canTransitionTo(ReservationStatus::Approved)) {
                throw new InvalidReservationTransitionException(
                    "Reservasi berstatus {$reservation->status->label()} tidak dapat disetujui."
                );
            }

            $room = $this->rooms->lockForReservation($reservation->room_id);

            $range = new DateRange($reservation->start_datetime, $reservation->end_datetime);
            $occupyingQuery = $this->reservations->occupyingQueryForRoom($room->id);

            if ($this->conflicts->hasConflict($occupyingQuery, $range, excludeId: $reservation->id)) {
                throw new ReservationConflictException(
                    "Tidak dapat menyetujui — jadwal bentrok dengan reservasi lain pada ruangan {$room->name}."
                );
            }

            return $this->reservations->updateStatus($reservation, [
                'status'      => ReservationStatus::Approved,
                'approved_by' => $data->approvedBy,
                'approved_at' => now(),
            ]);
        });

        event(new ReservationApproved($reservation));

        return $reservation;
    }
}
