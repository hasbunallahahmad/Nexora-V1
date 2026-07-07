<?php

declare(strict_types=1);

namespace App\Mobility\Actions;

use App\Mobility\Events\VehicleReservationSubmitted;
use App\Mobility\Exceptions\InvalidVehicleReservationTransitionException;
use App\Mobility\Exceptions\VehicleMaintenanceConflictException;
use App\Mobility\Exceptions\VehicleNotReservableException;
use App\Mobility\Exceptions\VehicleReservationConflictException;
use App\Mobility\Models\VehicleReservation;
use App\Mobility\Repositories\VehicleMaintenanceRepository;
use App\Mobility\Repositories\VehicleRepository;
use App\Mobility\Repositories\VehicleReservationRepository;
use App\Shared\DTO\DateRange;
use App\Shared\Enums\ReservationStatus;
use App\Shared\Services\ConflictDetectionService;
use Illuminate\Support\Facades\DB;

final class SubmitVehicleReservationAction
{
    public function __construct(
        private readonly VehicleRepository $vehicles,
        private readonly VehicleReservationRepository $reservations,
        private readonly VehicleMaintenanceRepository $maintenance,
        private readonly ConflictDetectionService $conflicts,
    ) {}

    public function execute(int $reservationId): VehicleReservation
    {
        $reservation = DB::transaction(function () use ($reservationId): VehicleReservation {
            $reservation = $this->reservations->findOrFail($reservationId);

            if (! $reservation->status->canTransitionTo(ReservationStatus::Submitted)) {
                throw new InvalidVehicleReservationTransitionException(
                    "Reservasi berstatus {$reservation->status->label()} tidak dapat diajukan."
                );
            }

            $vehicle = $this->vehicles->lockForReservation($reservation->vehicle_id);

            if (! $vehicle->status->isReservable()) {
                throw new VehicleNotReservableException(
                    "Kendaraan {$vehicle->name} berstatus {$vehicle->status->label()} dan tidak dapat direservasi."
                );
            }

            $range = new DateRange($reservation->start_datetime, $reservation->end_datetime);

            $occupyingQuery = $this->reservations->occupyingQueryForVehicle($vehicle->id);

            if ($this->conflicts->hasConflict($occupyingQuery, $range, excludeId: $reservation->id)) {
                throw new VehicleReservationConflictException(
                    "Jadwal bentrok dengan reservasi lain yang sudah diajukan/disetujui pada kendaraan {$vehicle->name}."
                );
            }

            $maintenanceQuery = $this->maintenance->queryForVehicle($vehicle->id);

            if ($this->conflicts->hasConflict($maintenanceQuery, $range)) {
                throw new VehicleMaintenanceConflictException(
                    "Kendaraan {$vehicle->name} sedang dijadwalkan perawatan pada rentang waktu tersebut."
                );
            }

            return $this->reservations->updateStatus($reservation, [
                'status' => ReservationStatus::Submitted,
            ]);
        });

        event(new VehicleReservationSubmitted($reservation));

        return $reservation;
    }
}
