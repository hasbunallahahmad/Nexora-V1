<?php

declare(strict_types=1);

namespace App\Mobility\Filament\Resources\Vehicles;

use App\Mobility\Filament\Resources\Vehicles\Pages\ListVehicles;
use App\Mobility\Filament\Resources\Vehicles\Schemas\VehicleForm;
use App\Mobility\Filament\Resources\Vehicles\Tables\VehiclesTable;
use App\Mobility\Models\Vehicle;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class VehicleResource extends Resource
{
    protected static ?string $model = Vehicle::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Truck;

    protected static ?string $navigationLabel = 'Kendaraan';

    protected static ?string $pluralLabel = 'Kendaraan';

    protected static ?string $slug = 'kendaraan';

    protected static string|UnitEnum|null $navigationGroup = 'Mobilitas';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return VehicleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VehiclesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVehicles::route('/'),
        ];
    }
}
