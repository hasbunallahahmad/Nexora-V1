<?php

declare(strict_types=1);

namespace App\Mobility\Actions;

use App\Mobility\DTO\CompleteVehicleReservationData;
use App\Mobility\Events\VehicleReservationCompleted;
use App\Mobility\Exceptions\InvalidVehicleReservationTransitionException;
use App\Mobility\Models\VehicleReservation;
use App\Mobility\Repositories\VehicleReservationRepository;
use App\Shared\Enums\ReservationStatus;
use InvalidArgumentException;

final class CompleteVehicleReservationAction
{
    public function __construct(
        private readonly VehicleReservationRepository $reservations,
    ) {}

    public function execute(CompleteVehicleReservationData $data): VehicleReservation
    {
        $reservation = $this->reservations->findOrFail($data->reservationId);

        if (! $reservation->status->canTransitionTo(ReservationStatus::Completed)) {
            throw new InvalidVehicleReservationTransitionException(
                "Reservasi berstatus {$reservation->status->label()} tidak dapat diselesaikan."
            );
        }

        if ($data->actualEndDatetime->lt($reservation->start_datetime)) {
            throw new InvalidArgumentException('Waktu selesai aktual tidak boleh sebelum waktu mulai.');
        }

        $reservation = $this->reservations->updateStatus($reservation, [
            'status'               => ReservationStatus::Completed,
            'actual_end_datetime'  => $data->actualEndDatetime,
        ]);

        event(new VehicleReservationCompleted($reservation));

        return $reservation;
    }
}
