<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Facility\Filament\Resources\RoomReservations\RoomReservationResource;
use App\Facility\Models\RoomReservation;
use App\Mobility\Filament\Resources\VehicleReservations\VehicleReservationResource;
use App\Mobility\Models\VehicleReservation;
use App\Shared\Enums\ReservationStatus;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class PendingApprovalsWidget extends Widget
{
    protected string $view = 'filament.widgets.pending-approvals';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        $user = Auth::user();

        return $user !== null
            && ($user->can('Approve:RoomReservation') || $user->can('Approve:VehicleReservation'));
    }

    protected function getViewData(): array
    {
        $user = Auth::user();

        $pending = collect();

        if ($user->can('Approve:RoomReservation')) {
            $pending = $pending->concat(
                RoomReservation::query()
                    ->where('status', ReservationStatus::Submitted)
                    ->with(['room', 'requestedBy'])
                    ->orderBy('created_at')
                    ->get()
                    ->map(fn(RoomReservation $r) => [
                        'type'        => 'Ruangan',
                        'icon'        => '🏢',
                        'title'       => $r->title,
                        'resource'    => $r->room?->name ?? '—',
                        'requestedBy' => $r->requestedBy?->name
                            ?? $r->guest_name . ($r->guest_instansi ? " ({$r->guest_instansi})" : ''),
                        'start'       => $r->start_datetime,
                        'url'         => RoomReservationResource::getUrl(),
                    ])
            );
        }

        if ($user->can('Approve:VehicleReservation')) {
            $pending = $pending->concat(
                VehicleReservation::query()
                    ->where('status', ReservationStatus::Submitted)
                    ->with(['vehicle', 'requestedBy'])
                    ->orderBy('created_at')
                    ->get()
                    ->map(fn(VehicleReservation $r) => [
                        'type'        => 'Kendaraan',
                        'icon'        => '🚗',
                        'title'       => $r->title,
                        'resource'    => $r->vehicle?->name ?? '—',
                        'requestedBy' => $r->requestedBy?->name ?? '—',
                        'start'       => $r->start_datetime,
                        'url'         => VehicleReservationResource::getUrl(),
                    ])
            );
        }

        return [
            'pending' => $pending->sortBy('start')->values(),
        ];
    }
}
