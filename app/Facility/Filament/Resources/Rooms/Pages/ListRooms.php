<?php

declare(strict_types=1);

namespace App\Facility\Filament\Resources\Rooms\Pages;

use App\Facility\Filament\Resources\Rooms\RoomResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRooms extends ListRecords
{
    protected static string $resource = RoomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Ruangan Baru')
                ->icon('heroicon-o-plus-circle'),
        ];
    }
}
