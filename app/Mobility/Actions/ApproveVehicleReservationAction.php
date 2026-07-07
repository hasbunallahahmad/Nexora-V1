<?php

declare(strict_types=1);

namespace App\Mobility\Actions;

use App\Mobility\DTO\ApproveVehicleReservationData;
use App\Mobility\Events\VehicleReservationApproved;
use App\Mobility\Exceptions\InvalidVehicleReservationTransitionException;
use App\Mobility\Exceptions\VehicleMaintenanceConflictException;
use App\Mobility\Exceptions\VehicleReservationConflictException;
use App\Mobility\Models\VehicleReservation;
use App\Mobility\Repositories\VehicleMaintenanceRepository;
use App\Mobility\Repositories\VehicleRepository;
use App\Mobility\Repositories\VehicleReservationRepository;
use App\Shared\DTO\DateRange;
use App\Shared\Enums\ReservationStatus;
use App\Shared\Services\ConflictDetectionService;
use Illuminate\Support\Facades\DB;

final class ApproveVehicleReservationAction
{
    public function __construct(
        private readonly VehicleRepository $vehicles,
        private readonly VehicleReservationRepository $reservations,
        private readonly VehicleMaintenanceRepository $maintenance,
        private readonly ConflictDetectionService $conflicts,
    ) {}

    public function execute(ApproveVehicleReservationData $data): VehicleReservation
    {
        $reservation = DB::transaction(function () use ($data): VehicleReservation {
            $reservation = $this->reservations->findOrFail($data->reservationId);

            if (! $reservation->status->canTransitionTo(ReservationStatus::Approved)) {
                throw new InvalidVehicleReservationTransitionException(
                    "Reservasi berstatus {$reservation->status->label()} tidak dapat disetujui."
                );
            }

            $vehicle = $this->vehicles->lockForReservation($reservation->vehicle_id);

            $range = new DateRange($reservation->start_datetime, $reservation->end_datetime);

            $occupyingQuery = $this->reservations->occupyingQueryForVehicle($vehicle->id);

            if ($this->conflicts->hasConflict($occupyingQuery, $range, excludeId: $reservation->id)) {
                throw new VehicleReservationConflictException(
                    "Tidak dapat menyetujui — jadwal bentrok dengan reservasi lain pada kendaraan {$vehicle->name}."
                );
            }

            $maintenanceQuery = $this->maintenance->queryForVehicle($vehicle->id);

            if ($this->conflicts->hasConflict($maintenanceQuery, $range)) {
                throw new VehicleMaintenanceConflictException(
                    "Tidak dapat menyetujui — kendaraan {$vehicle->name} dijadwalkan perawatan pada rentang waktu tersebut."
                );
            }

            return $this->reservations->updateStatus($reservation, [
                'status'      => ReservationStatus::Approved,
                'approved_by' => $data->approvedBy,
                'approved_at' => now(),
            ]);
        });

        event(new VehicleReservationApproved($reservation));

        return $reservation;
    }
}
