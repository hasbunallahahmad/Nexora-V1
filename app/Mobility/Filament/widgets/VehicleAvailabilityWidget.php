<?php

declare(strict_types=1);

namespace App\Mobility\Filament\Widgets;

use App\Mobility\Enums\VehicleStatus;
use App\Mobility\Models\Vehicle;
use App\Shared\Enums\ReservationStatus;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class VehicleAvailabilityWidget extends Widget
{
    protected string $view = 'mobility.filament.widgets.vehicle-availability';

    protected int|string|array $columnSpan = [
        'md' => 1,
        'xl' => 1,
    ];

    public static function canView(): bool
    {
        return Auth::user()?->can('viewAny', Vehicle::class) ?? false;
    }

    protected function getViewData(): array
    {
        $now = now();

        $vehicles = Vehicle::query()
            ->where('status', VehicleStatus::Active)
            ->orderBy('name')
            ->get()
            ->map(function (Vehicle $vehicle) use ($now) {
                $current = $vehicle->reservations()
                    ->where('status', ReservationStatus::Approved)
                    ->where('start_datetime', '<=', $now)
                    ->where('end_datetime', '>=', $now)
                    ->with('requestedBy')
                    ->first();

                $onMaintenance = $vehicle->maintenancePeriods()
                    ->where('start_datetime', '<=', $now)
                    ->where('end_datetime', '>=', $now)
                    ->exists();

                return [
                    'vehicle'       => $vehicle,
                    'inUse'         => $current !== null,
                    'current'       => $current,
                    'onMaintenance' => $onMaintenance,
                ];
            });

        return [
            'vehicles'  => $vehicles,
            'canManage' => Auth::user()->can('create', Vehicle::class),
        ];
    }
}
