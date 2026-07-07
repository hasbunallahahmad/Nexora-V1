<?php

declare(strict_types=1);

namespace App\Mobility\Services;

use App\Mobility\Actions\ApproveVehicleReservationAction;
use App\Mobility\Actions\CancelVehicleReservationAction;
use App\Mobility\Actions\CreateDraftVehicleReservationAction;
use App\Mobility\Actions\RejectVehicleReservationAction;
use App\Mobility\Actions\SubmitVehicleReservationAction;
use App\Mobility\DTO\ApproveVehicleReservationData;
use App\Mobility\DTO\CreateVehicleReservationData;
use App\Mobility\DTO\RejectVehicleReservationData;
use App\Mobility\Models\VehicleReservation;

final class VehicleReservationService
{
    public function __construct(
        private readonly CreateDraftVehicleReservationAction $createDraftAction,
        private readonly SubmitVehicleReservationAction $submitAction,
        private readonly ApproveVehicleReservationAction $approveAction,
        private readonly RejectVehicleReservationAction $rejectAction,
        private readonly CancelVehicleReservationAction $cancelAction,
    ) {}

    public function createDraft(CreateVehicleReservationData $data): VehicleReservation
    {
        return $this->createDraftAction->execute($data);
    }

    public function submit(int $reservationId): VehicleReservation
    {
        return $this->submitAction->execute($reservationId);
    }

    public function approve(ApproveVehicleReservationData $data): VehicleReservation
    {
        return $this->approveAction->execute($data);
    }

    public function reject(RejectVehicleReservationData $data): VehicleReservation
    {
        return $this->rejectAction->execute($data);
    }

    public function cancel(int $reservationId): VehicleReservation
    {
        return $this->cancelAction->execute($reservationId);
    }
}
