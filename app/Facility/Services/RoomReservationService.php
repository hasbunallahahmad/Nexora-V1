<?php

declare(strict_types=1);

namespace App\Facility\Services;

use App\Facility\Actions\ApproveReservationAction;
use App\Facility\Actions\CancelReservationAction;
use App\Facility\Actions\CreateDraftReservationAction;
use App\Facility\Actions\RejectReservationAction;
use App\Facility\Actions\SubmitReservationAction;
use App\Facility\DTO\ApproveReservationData;
use App\Facility\DTO\CreateRoomReservationData;
use App\Facility\DTO\RejectReservationData;
use App\Facility\Models\RoomReservation;

final class RoomReservationService
{
    public function __construct(
        private readonly CreateDraftReservationAction $createDraftAction,
        private readonly SubmitReservationAction $submitAction,
        private readonly ApproveReservationAction $approveAction,
        private readonly RejectReservationAction $rejectAction,
        private readonly CancelReservationAction $cancelAction,
    ) {}

    public function createDraft(CreateRoomReservationData $data): RoomReservation
    {
        return $this->createDraftAction->execute($data);
    }

    public function submit(int $reservationId): RoomReservation
    {
        return $this->submitAction->execute($reservationId);
    }

    public function approve(ApproveReservationData $data): RoomReservation
    {
        return $this->approveAction->execute($data);
    }

    public function reject(RejectReservationData $data): RoomReservation
    {
        return $this->rejectAction->execute($data);
    }

    public function cancel(int $reservationId): RoomReservation
    {
        return $this->cancelAction->execute($reservationId);
    }
}
