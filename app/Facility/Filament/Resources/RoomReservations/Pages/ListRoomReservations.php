<?php

declare(strict_types=1);

namespace App\Facility\Filament\Resources\RoomReservations\Pages;

use App\Facility\DTO\CreateRoomReservationData;
use App\Facility\Filament\Resources\RoomReservations\RoomReservationResource;
use App\Facility\Filament\Resources\RoomReservations\Schemas\RoomReservationForm;
use App\Facility\Services\RoomReservationService;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ListRoomReservations extends ListRecords
{
    protected static string $resource = RoomReservationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Buat Reservasi')
                ->modalWidth('2xl')
                ->schema(RoomReservationForm::getComponents())
                ->using(function (array $data, RoomReservationService $service): Model {
                    return $service->createDraft(new CreateRoomReservationData(
                        roomId: (int) $data['room_id'],
                        agendaId: $data['agenda_id'] ?? null,
                        requestedBy: Auth::id(),
                        title: $data['title'],
                        purpose: $data['purpose'] ?? null,
                        startDatetime: \Carbon\Carbon::parse($data['start_datetime']),
                        endDatetime: \Carbon\Carbon::parse($data['end_datetime']),
                    ));
                }),
        ];
    }
}
