<?php

declare(strict_types=1);

namespace App\Mobility\Services\Sources;

use App\Calendar\Contracts\CalendarEventSource;
use App\Calendar\DTO\CalendarEventData;
use App\Calendar\DTO\CalendarQuery;
use App\Calendar\Enums\CalendarAudience;
use App\Mobility\Models\VehicleReservation;
use App\Shared\Enums\ReservationStatus;
use Illuminate\Support\Collection;

final class VehicleReservationEventSource implements CalendarEventSource
{
    private const PALETTE = [
        '#8e44ad',
        '#16a085',
        '#d35400',
        '#2980b9',
        '#c0392b',
        '#27ae60',
        '#2c3e50',
        '#f39c12',
    ];

    public function sourceType(): string
    {
        return 'vehicle_reservation';
    }

    public function events(CalendarQuery $query): Collection
    {
        // Mobility khusus internal — tidak ada audience Public yang relevan
        // secara fungsional (tidak ada halaman publik Mobility), tapi tetap
        // dibedakan untuk konsistensi kontrak CalendarEventSource: Admin
        // melihat proses berjalan, Public (jika suatu saat dipanggil) hanya
        // melihat yang final.
        $statuses = match ($query->audience) {
            CalendarAudience::Public => [ReservationStatus::Approved, ReservationStatus::Completed],
            CalendarAudience::Admin => [
                ReservationStatus::Submitted,
                ReservationStatus::Approved,
                ReservationStatus::Completed,
            ],
        };

        return VehicleReservation::query()
            ->whereIn('status', array_map(fn($status) => $status->value, $statuses))
            ->where('start_datetime', '<', $query->end)
            ->where('end_datetime', '>', $query->start)
            ->with(['vehicle', 'requestedBy'])
            ->get()
            ->map(fn(VehicleReservation $reservation): CalendarEventData => new CalendarEventData(
                sourceType: $this->sourceType(),
                id: $reservation->id,
                title: $reservation->title,
                start: $reservation->start_datetime,
                end: $reservation->end_datetime,
                backgroundColor: $this->colorForVehicle($reservation->vehicle_id),
                extendedProps: [
                    'vehicle'     => $reservation->vehicle?->name,
                    'driver'      => $reservation->vehicle?->driver_name,
                    'destination' => $reservation->destination,
                    'requestedBy' => $reservation->requestedBy?->name,
                    'status'      => $reservation->status->label(),
                ],
            ))
            ->values();
    }

    private function colorForVehicle(int $vehicleId): string
    {
        return self::PALETTE[$vehicleId % count(self::PALETTE)];
    }
}
