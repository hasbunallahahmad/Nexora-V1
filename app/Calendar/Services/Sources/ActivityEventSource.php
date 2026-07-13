<?php

declare(strict_types=1);

namespace App\Calendar\Services\Sources;

use App\Calendar\Contracts\CalendarEventSource;
use App\Calendar\DTO\CalendarEventData;
use App\Calendar\DTO\CalendarQuery;
use App\Calendar\Enums\CalendarAudience;
use App\Http\Resources\AgendaCalendarResource;
use App\Activity\Models\Agenda;
use Carbon\Carbon;
use Illuminate\Support\Collection;

final class ActivityEventSource implements CalendarEventSource

{
    public function sourceType(): string
    {
        return 'activity';
    }

    public function events(CalendarQuery $query): Collection
    {
        return match ($query->audience) {
            CalendarAudience::Public => $this->publicEvents($query),
            CalendarAudience::Admin => $this->adminEvents($query),
        };
    }

    private function publicEvents(CalendarQuery $query): Collection
    {
        return Agenda::published()
            ->betweenDates($query->start->toDateString(), $query->end->toDateString())
            ->with('bidang')
            ->get()
            ->map(fn(Agenda $agenda): CalendarEventData => new CalendarEventData(
                sourceType: $this->sourceType(),
                id: $agenda->id,
                title: $agenda->judul_agenda,
                start: $agenda->start_date,
                end: $agenda->end_date,
                backgroundColor: AgendaCalendarResource::colorForBidang($agenda->bidang->first()),
                extendedProps: [
                    'waktu_mulai'   => $agenda->waktu_mulai,
                    'waktu_selesai' => $agenda->waktu_selesai,
                    'lokasi'        => $agenda->location,
                    'deskripsi'     => $agenda->deskripsi,
                    'bidang'        => $agenda->bidang
                        ->map(fn($b) => ['id' => $b->id, 'nama' => $b->nama_bidang])
                        ->values(),
                    'start_format'  => $agenda->start_format,
                    'end_format'    => $agenda->end_format,
                    'slug'          => $agenda->slug,
                ],
            ))
            ->values();
    }

    private function adminEvents(CalendarQuery $query): Collection
    {
        return Agenda::query()
            ->where('start_date', '>=', $query->start)
            ->where('start_date', '<=', $query->end)
            ->with('bidang')
            ->get()
            ->map(function (Agenda $agenda): CalendarEventData {
                $startJakarta = Carbon::parse($agenda->start_date)->setTimezone('Asia/Jakarta');
                $endJakarta = $agenda->end_date
                    ? Carbon::parse($agenda->end_date)->setTimezone('Asia/Jakarta')
                    : null;

                return new CalendarEventData(
                    sourceType: $this->sourceType(),
                    id: $agenda->id,
                    title: strip_tags($agenda->judul_agenda),
                    start: $agenda->start_date,
                    end: $agenda->end_date,
                    extendedProps: [
                        'description' => strip_tags((string) $agenda->deskripsi),
                        'location'    => strip_tags((string) $agenda->location),
                        'bidang'      => $agenda->bidang->pluck('nama_bidang')->implode(', '),
                        'start_date'  => $startJakarta->format('Y-m-d H:i'),
                        'end_date'    => $endJakarta?->format('Y-m-d H:i'),
                    ],
                );
            })
            ->values();
    }
}
