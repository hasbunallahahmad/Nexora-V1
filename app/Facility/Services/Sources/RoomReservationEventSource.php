<?php

declare(strict_types=1);

namespace App\Facility\Services\Sources;

use App\Calendar\Contracts\CalendarEventSource;
use App\Calendar\DTO\CalendarEventData;
use App\Calendar\DTO\CalendarQuery;
use App\Calendar\Enums\CalendarAudience;
use App\Facility\Enums\ReservationStatus;
use App\Facility\Models\RoomReservation;
use Illuminate\Support\Collection;

final class RoomReservationEventSource implements CalendarEventSource
{
    private const PALETTE = [
        '#0f1f3d',
        '#2980b9',
        '#27ae60',
        '#8e44ad',
        '#c0392b',
        '#16a085',
        '#d35400',
        '#2c3e50',
    ];

    public function sourceType(): string
    {
        return 'room_reservation';
    }

    public function events(CalendarQuery $query): Collection
    {
        // Public hanya melihat yang sudah final (Approved/Completed) — jangan
        // bocorkan reservasi yang masih dalam proses persetujuan ke publik.
        // Admin butuh melihat Submitted juga, supaya tahu ada pengajuan yang
        // menunggu keputusan saat melihat kalender.
        $statuses = match ($query->audience) {
            CalendarAudience::Public => [ReservationStatus::Approved, ReservationStatus::Completed],
            CalendarAudience::Admin => [
                ReservationStatus::Submitted,
                ReservationStatus::Approved,
                ReservationStatus::Completed,
            ],
        };

        return RoomReservation::query()
            ->whereIn('status', array_map(fn($status) => $status->value, $statuses))
            ->where('start_datetime', '<', $query->end)
            ->where('end_datetime', '>', $query->start)
            ->with(['room', 'requestedBy'])
            ->get()
            ->map(fn(RoomReservation $reservation): CalendarEventData => new CalendarEventData(
                sourceType: $this->sourceType(),
                id: $reservation->id,
                title: $reservation->title,
                start: $reservation->start_datetime,
                end: $reservation->end_datetime,
                backgroundColor: $this->colorForRoom($reservation->room_id),
                extendedProps: [
                    'room'        => $reservation->room?->name,
                    'requestedBy' => $reservation->requestedBy?->name,
                    'purpose'     => $reservation->purpose,
                    'status'      => $reservation->status->label(),
                ],
            ))
            ->values();
    }

    private function colorForRoom(int $roomId): string
    {
        return self::PALETTE[$roomId % count(self::PALETTE)];
    }
}
