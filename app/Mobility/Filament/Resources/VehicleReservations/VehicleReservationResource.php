<?php

declare(strict_types=1);

namespace App\Mobility\Filament\Resources\VehicleReservations;

use App\Mobility\Filament\Resources\VehicleReservations\Pages\ListVehicleReservations;
use App\Mobility\Filament\Resources\VehicleReservations\Schemas\VehicleReservationForm;
use App\Mobility\Filament\Resources\VehicleReservations\Tables\VehicleReservationsTable;
use App\Mobility\Models\VehicleReservation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class VehicleReservationResource extends Resource
{
    protected static ?string $model = VehicleReservation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::MapPin;

    protected static ?string $navigationLabel = 'Reservasi Kendaraan';

    protected static string|UnitEnum|null $navigationGroup = 'Mobilitas';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return VehicleReservationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VehicleReservationsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVehicleReservations::route('/'),
        ];
    }
}
