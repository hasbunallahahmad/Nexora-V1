<?php

declare(strict_types=1);

namespace App\Mobility\Actions;

use App\Mobility\DTO\CreateVehicleReservationData;
use App\Mobility\Models\VehicleReservation;
use App\Mobility\Repositories\VehicleReservationRepository;
use App\Shared\Enums\ReservationStatus;
use InvalidArgumentException;

final class CreateDraftVehicleReservationAction
{
    public function __construct(
        private readonly VehicleReservationRepository $reservations,
    ) {}

    public function execute(CreateVehicleReservationData $data): VehicleReservation
    {
        if ($data->startDatetime->gte($data->endDatetime)) {
            throw new InvalidArgumentException('Tanggal mulai harus sebelum tanggal selesai.');
        }

        return $this->reservations->create([
            'vehicle_id'     => $data->vehicleId,
            'agenda_id'      => $data->agendaId,
            'requested_by'   => $data->requestedBy,
            'title'          => $data->title,
            'destination'    => $data->destination,
            'purpose'        => $data->purpose,
            'start_datetime' => $data->startDatetime,
            'end_datetime'   => $data->endDatetime,
            'notes'          => $data->notes,
            'status'         => ReservationStatus::Draft,
        ]);
    }
}
