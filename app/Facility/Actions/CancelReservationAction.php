<?php

declare(strict_types=1);

namespace App\Facility\Actions;

use App\Facility\Enums\ReservationStatus;
use App\Facility\Exceptions\InvalidReservationTransitionException;
use App\Facility\Repositories\RoomReservationRepository;
use App\Models\Facility\Models\RoomReservation;

final class CancelReservationAction
{
    public function __construct(
        private readonly RoomReservationRepository $reservations,
    ) {}

    public function execute(int $reservationId): RoomReservation
    {
        $reservation = $this->reservations->findOrFail($reservationId);

        if (! $reservation->status->canTransitionTo(ReservationStatus::Cancelled)) {
            throw new InvalidReservationTransitionException(
                "Reservasi berstatus {$reservation->status->label()} tidak dapat dibatalkan."
            );
        }

        return $this->reservations->updateStatus($reservation, [
            'status'       => ReservationStatus::Cancelled,
            'cancelled_at' => now(),
        ]);
    }
}
