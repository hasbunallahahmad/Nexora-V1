<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Calendar\DTO\CalendarQuery;
use App\Calendar\Enums\CalendarAudience;
use App\Calendar\Services\CalendarAggregationService;
use App\Facility\DTO\CreateRoomReservationData;
use App\Facility\Enums\RoomStatus;
use App\Facility\Exceptions\ReservationConflictException;
use App\Facility\Exceptions\RoomNotReservableException;
use App\Facility\Models\Room;
use App\Facility\Services\RoomReservationService;
use App\Http\Requests\StorePublicRoomReservationRequest;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PublicRoomReservationController extends Controller
{
    public function __construct(
        private readonly RoomReservationService $reservations,
        private readonly CalendarAggregationService $calendar,
    ) {}

    public function index(): View
    {
        $rooms = Room::query()->where('status', RoomStatus::Active)->get();

        return view('room-reservation.index', compact('rooms'));
    }

    public function kalenderFeed(): JsonResponse
    {
        $start = Carbon::now()->startOfMonth()->subMonth();
        $end = Carbon::now()->endOfMonth()->addMonth();

        $query = new CalendarQuery($start, $end, CalendarAudience::Public);

        $events = $this->calendar->getEvents($query)
            ->filter(fn($event) => $event->sourceType === 'room_reservation')
            ->map(function ($event) {
                $startJakarta = $event->start->copy()->setTimezone('Asia/Jakarta');
                $endJakarta = $event->end?->copy()->setTimezone('Asia/Jakarta');

                return [
                    'id'              => $event->id,
                    'title'           => $event->title,
                    'start'           => $event->start->toIso8601String(),
                    'end'             => $event->end?->toIso8601String(),
                    'backgroundColor' => $event->backgroundColor,
                    // 'guestName'       => $event->guestName,
                    'extendedProps'   => array_merge($event->extendedProps, [
                        'start_format'  => $startJakarta->translatedFormat('l, d F Y'),
                        'end_format'    => ($endJakarta && ! $endJakarta->isSameDay($startJakarta))
                            ? $endJakarta->translatedFormat('l, d F Y')
                            : null,
                        'waktu_mulai'   => $startJakarta->format('H:i') . ' WIB',
                        'waktu_selesai' => $endJakarta?->format('H:i') . ' WIB',
                    ]),
                ];
            })
            ->values();

        return response()->json($events)
            ->header('Cache-Control', 'no-store, private')
            ->header('X-Content-Type-Options', 'nosniff');
    }

    public function store(StorePublicRoomReservationRequest $request): RedirectResponse
    {
        $data = $request->validated();

        try {
            $room = Room::findOrFail((int) $data['room_id']);

            $reservation = $this->reservations->createDraft(new CreateRoomReservationData(
                roomId: $room->id,
                agendaId: null,
                requestedBy: null,
                title: $data['title'],
                purpose: $data['purpose'] ?? null,
                startDatetime: Carbon::parse($data['start_datetime']),
                endDatetime: Carbon::parse($data['end_datetime']),
                guestName: $data['guest_name'],
                guestContact: $data['guest_contact'],
                guestInstansi: $data['guest_instansi'] ?? null,
            ));

            $this->reservations->submit($reservation->id);

            return back()->with('reservation_success', [
                'message'      => 'Reservasi berhasil diajukan. Admin dinas akan menghubungi Anda via WhatsApp untuk konfirmasi.',
                'guest_name'   => $data['guest_name'],
                'room_name'    => $room->name,
                'start_format' => Carbon::parse($data['start_datetime'])->translatedFormat('l, d F Y H:i'),
            ]);
        } catch (ReservationConflictException | RoomNotReservableException $e) {
            return back()->withErrors(['conflict' => $e->getMessage()])->withInput();
        }
    }
}
