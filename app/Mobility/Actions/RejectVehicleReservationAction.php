<?php

declare(strict_types=1);

namespace App\Mobility\Actions;

use App\Mobility\DTO\RejectVehicleReservationData;
use App\Mobility\Events\VehicleReservationRejected;
use App\Mobility\Exceptions\InvalidVehicleReservationTransitionException;
use App\Mobility\Models\VehicleReservation;
use App\Mobility\Repositories\VehicleReservationRepository;
use App\Shared\Enums\ReservationStatus;

final class RejectVehicleReservationAction
{
    public function __construct(
        private readonly VehicleReservationRepository $reservations,
    ) {}

    public function execute(RejectVehicleReservationData $data): VehicleReservation
    {
        $reservation = $this->reservations->findOrFail($data->reservationId);

        if (! $reservation->status->canTransitionTo(ReservationStatus::Rejected)) {
            throw new InvalidVehicleReservationTransitionException(
                "Reservasi berstatus {$reservation->status->label()} tidak dapat ditolak."
            );
        }

        $reservation = $this->reservations->updateStatus($reservation, [
            'status'          => ReservationStatus::Rejected,
            'rejected_reason' => $data->reason,
        ]);

        event(new VehicleReservationRejected($reservation));

        return $reservation;
    }
}
