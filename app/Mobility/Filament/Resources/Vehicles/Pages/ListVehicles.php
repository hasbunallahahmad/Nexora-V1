<?php

declare(strict_types=1);

namespace App\Mobility\Filament\Resources\Vehicles\Pages;

use App\Mobility\Filament\Resources\Vehicles\VehicleResource;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListVehicles extends ListRecords
{
    protected static string $resource = VehicleResource::class;

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         CreateAction::make()
    //             ->label('Tambah Kendaraan')
    //             ->icon('heroicon-o-plus-circle')
    //             ->modalHeading('Tambah Kendaraan Baru')
    //             ->modalWidth('lg')
    //             ->createAnother(false)
    //             ->action(function (array $data, VehicleResource $resource) {
    //                 $vehicle = $resource->create($data);

    //                 Notification::make()
    //                     ->title('Berhasil!')
    //                     ->body("Kendaraan {$vehicle->name} berhasil ditambahkan.")
    //                     ->success()
    //                     ->duration(3000)
    //                     ->send();
    //             }),
    //     ];
    // }
}
