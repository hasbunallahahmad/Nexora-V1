<?php

declare(strict_types=1);

namespace App\Facility\Filament\Widgets;

use App\Facility\Enums\RoomStatus;
use App\Facility\Models\Room;
use App\Shared\Enums\ReservationStatus;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class RoomAvailabilityWidget extends Widget
{
    protected string $view = 'facility.filament.widgets.room-availability';

    protected int|string|array $columnSpan = [
        'md' => 1,
        'xl' => 1,
    ];

    public static function canView(): bool
    {
        return Auth::user()?->can('viewAny', Room::class) ?? false;
    }

    protected function getViewData(): array
    {
        $now = now();

        $rooms = Room::query()
            ->where('status', RoomStatus::Active)
            ->orderBy('name')
            ->get()
            ->map(function (Room $room) use ($now) {
                $current = $room->reservations()
                    ->where('status', ReservationStatus::Approved)
                    ->where('start_datetime', '<=', $now)
                    ->where('end_datetime', '>=', $now)
                    ->with('requestedBy')
                    ->first();

                return [
                    'room'    => $room,
                    'inUse'   => $current !== null,
                    'current' => $current,
                ];
            });

        return [
            'rooms'      => $rooms,
            'canManage'  => Auth::user()->can('create', Room::class),
        ];
    }
}
