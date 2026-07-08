<?php

declare(strict_types=1);

namespace App\Mobility\Filament\Resources\VehicleReservations\Pages;

use App\Mobility\DTO\CreateVehicleReservationData;
use App\Mobility\Filament\Resources\VehicleReservations\Schemas\VehicleReservationForm;
use App\Mobility\Filament\Resources\VehicleReservations\VehicleReservationResource;
use App\Mobility\Services\VehicleReservationService;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ListVehicleReservations extends ListRecords
{
    protected static string $resource = VehicleReservationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Buat Reservasi')
                ->modalWidth('2xl')
                ->schema(VehicleReservationForm::getComponents())
                ->using(function (array $data, VehicleReservationService $service): Model {
                    return $service->createDraft(new CreateVehicleReservationData(
                        vehicleId: (int) $data['vehicle_id'],
                        agendaId: $data['agenda_id'] ?? null,
                        requestedBy: Auth::id(),
                        title: $data['title'],
                        destination: $data['destination'] ?? null,
                        purpose: $data['purpose'] ?? null,
                        startDatetime: \Carbon\Carbon::parse($data['start_datetime']),
                        endDatetime: \Carbon\Carbon::parse($data['end_datetime']),
                    ));
                }),
        ];
    }
}
