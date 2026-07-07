<?php

declare(strict_types=1);

namespace App\Facility\Actions;

use App\Shared\Enums\ReservationStatus;
use App\Facility\Events\ReservationCancelled;
use App\Facility\Exceptions\InvalidReservationTransitionException;
use App\Facility\Models\RoomReservation;
use App\Facility\Repositories\RoomReservationRepository;

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

        $reservation = $this->reservations->updateStatus($reservation, [
            'status'       => ReservationStatus::Cancelled,
            'cancelled_at' => now(),
        ]);

        event(new ReservationCancelled($reservation));

        return $reservation;
    }
}
