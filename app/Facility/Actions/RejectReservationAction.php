<?php

declare(strict_types=1);

namespace App\Facility\Actions;

use App\Facility\DTO\RejectReservationData;
use App\Shared\Enums\ReservationStatus;
use App\Facility\Events\ReservationRejected;
use App\Facility\Exceptions\InvalidReservationTransitionException;
use App\Facility\Models\RoomReservation;
use App\Facility\Repositories\RoomReservationRepository;

final class RejectReservationAction
{
    public function __construct(
        private readonly RoomReservationRepository $reservations,
    ) {}

    public function execute(RejectReservationData $data): RoomReservation
    {
        $reservation = $this->reservations->findOrFail($data->reservationId);

        if (! $reservation->status->canTransitionTo(ReservationStatus::Rejected)) {
            throw new InvalidReservationTransitionException(
                "Reservasi berstatus {$reservation->status->label()} tidak dapat ditolak."
            );
        }

        $reservation = $this->reservations->updateStatus($reservation, [
            'status'          => ReservationStatus::Rejected,
            'rejected_reason' => $data->reason,
        ]);

        event(new ReservationRejected($reservation));

        return $reservation;
    }
}
