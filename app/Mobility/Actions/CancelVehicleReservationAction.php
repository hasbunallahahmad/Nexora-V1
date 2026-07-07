<?php

declare(strict_types=1);

namespace App\Mobility\Actions;

use App\Mobility\Events\VehicleReservationCancelled;
use App\Mobility\Exceptions\InvalidVehicleReservationTransitionException;
use App\Mobility\Models\VehicleReservation;
use App\Mobility\Repositories\VehicleReservationRepository;
use App\Shared\Enums\ReservationStatus;

final class CancelVehicleReservationAction
{
    public function __construct(
        private readonly VehicleReservationRepository $reservations,
    ) {}

    public function execute(int $reservationId): VehicleReservation
    {
        $reservation = $this->reservations->findOrFail($reservationId);

        if (! $reservation->status->canTransitionTo(ReservationStatus::Cancelled)) {
            throw new InvalidVehicleReservationTransitionException(
                "Reservasi berstatus {$reservation->status->label()} tidak dapat dibatalkan."
            );
        }

        $reservation = $this->reservations->updateStatus($reservation, [
            'status'       => ReservationStatus::Cancelled,
            'cancelled_at' => now(),
        ]);

        event(new VehicleReservationCancelled($reservation));

        return $reservation;
    }
}
