<?php

declare(strict_types=1);

namespace App\Facility\Actions;

use App\Facility\DTO\CreateRoomReservationData;
use App\Shared\Enums\ReservationStatus;
use App\Facility\Models\RoomReservation;
use App\Facility\Repositories\RoomReservationRepository;
use InvalidArgumentException;

final class CreateDraftReservationAction
{
    public function __construct(
        private readonly RoomReservationRepository $reservations,
    ) {}

    public function execute(CreateRoomReservationData $data): RoomReservation
    {
        if ($data->startDatetime->gte($data->endDatetime)) {
            throw new InvalidArgumentException('Tanggal mulai harus sebelum tanggal selesai.');
        }

        if ($data->requestedBy === null && empty($data->guestName)) {
            throw new InvalidArgumentException('Identitas pemohon wajib diisi (akun pengguna atau nama tamu).');
        }

        return $this->reservations->create([
            'room_id'        => $data->roomId,
            'agenda_id'      => $data->agendaId,
            'requested_by'   => $data->requestedBy,
            'title'          => $data->title,
            'purpose'        => $data->purpose,
            'start_datetime' => $data->startDatetime,
            'end_datetime'   => $data->endDatetime,
            'notes'          => $data->notes,
            'guest_name'     => $data->guestName,
            'guest_contact'  => $data->guestContact,
            'guest_instansi' => $data->guestInstansi,
            'status'         => ReservationStatus::Draft,
        ]);
    }
}
