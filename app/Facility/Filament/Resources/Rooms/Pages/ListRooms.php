<?php

declare(strict_types=1);

namespace App\Facility\Filament\Resources\Rooms\Pages;

use App\Facility\Filament\Resources\Rooms\RoomResource;
use Filament\Resources\Pages\ListRecords;

class ListRooms extends ListRecords
{
    protected static string $resource = RoomResource::class;
}
