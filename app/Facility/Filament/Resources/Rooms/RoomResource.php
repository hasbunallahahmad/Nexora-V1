<?php

declare(strict_types=1);

namespace App\Facility\Filament\Resources\Rooms;

use App\Facility\Filament\Resources\Rooms\Pages\ListRooms;
use App\Facility\Filament\Resources\Rooms\Schemas\RoomForm;
use App\Facility\Filament\Resources\Rooms\Tables\RoomsTable;
use App\Facility\Models\Room;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class RoomResource extends Resource
{
    protected static ?string $model = Room::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::BuildingOffice2;

    protected static ?string $navigationLabel = 'Ruangan';

    protected static string|UnitEnum|null $navigationGroup = 'Fasilitas';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return RoomForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RoomsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRooms::route('/'),
        ];
    }

    public static function getRecordRouteKeyName(): ?string
    {
        return 'slug';
    }
}
