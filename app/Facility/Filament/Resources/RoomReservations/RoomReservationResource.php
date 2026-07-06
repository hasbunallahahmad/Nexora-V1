<?php

declare(strict_types=1);

namespace App\Facility\Filament\Resources\RoomReservations;

use App\Facility\Filament\Resources\RoomReservations\Pages;
use App\Facility\Filament\Resources\RoomReservations\Schemas\RoomReservationForm;
use App\Facility\Filament\Resources\RoomReservations\Tables\RoomReservationsTable;
use App\Facility\Models\RoomReservation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class RoomReservationResource extends Resource
{
    protected static ?string $model = RoomReservation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::CalendarDateRange;

    protected static ?string $navigationLabel = 'Reservasi Ruangan';

    protected static string|UnitEnum|null $navigationGroup = 'Fasilitas';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return RoomReservationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RoomReservationsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' =>  Pages\ListRoomReservations::route('/'),
        ];
    }
}
