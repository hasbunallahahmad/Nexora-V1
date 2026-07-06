<?php

declare(strict_types=1);

namespace App\Facility\Actions;

use App\Facility\Enums\ReservationStatus;
use App\Facility\Exceptions\InvalidReservationTransitionException;
use App\Facility\Exceptions\ReservationConflictException;
use App\Facility\Exceptions\RoomNotReservableException;
use App\Facility\Repositories\RoomRepository;
use App\Facility\Repositories\RoomReservationRepository;
use App\Facility\Models\RoomReservation;
use App\Shared\DTO\DateRange;
use App\Shared\Services\ConflictDetectionService;
use Illuminate\Support\Facades\DB;

final class SubmitReservationAction
{
    public function __construct(
        private readonly RoomRepository $rooms,
        private readonly RoomReservationRepository $reservations,
        private readonly ConflictDetectionService $conflicts,
    ) {}

    public function execute(int $reservationId): RoomReservation
    {
        return DB::transaction(function () use ($reservationId): RoomReservation {
            $reservation = $this->reservations->findOrFail($reservationId);

            if (! $reservation->status->canTransitionTo(ReservationStatus::Submitted)) {
                throw new InvalidReservationTransitionException(
                    "Reservasi berstatus {$reservation->status->label()} tidak dapat diajukan."
                );
            }

            // Mutex: kunci baris Room agar submit lain untuk ruangan yang
            // sama harus antre sampai transaksi ini selesai.
            $room = $this->rooms->lockForReservation($reservation->room_id);

            if (! $room->status->isReservable()) {
                throw new RoomNotReservableException(
                    "Ruangan {$room->name} berstatus {$room->status->label()} dan tidak dapat direservasi."
                );
            }

            $range = new DateRange($reservation->start_datetime, $reservation->end_datetime);
            $occupyingQuery = $this->reservations->occupyingQueryForRoom($room->id);

            if ($this->conflicts->hasConflict($occupyingQuery, $range, excludeId: $reservation->id)) {
                throw new ReservationConflictException(
                    "Jadwal bentrok dengan reservasi lain yang sudah diajukan/disetujui pada ruangan {$room->name}."
                );
            }

            return $this->reservations->updateStatus($reservation, [
                'status' => ReservationStatus::Submitted,
            ]);
        });
    }
}
