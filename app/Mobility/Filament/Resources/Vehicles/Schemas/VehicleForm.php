<?php

declare(strict_types=1);

namespace App\Mobility\Filament\Resources\Vehicles\Schemas;

use App\Mobility\Enums\VehicleStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class VehicleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nama Kendaraan')
                ->required()
                ->maxLength(100),

            TextInput::make('plate_number')
                ->label('Nomor Polisi')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(20),

            TextInput::make('type')
                ->label('Jenis')
                ->maxLength(50)
                ->placeholder('MPV, Bus, Pickup, dsb'),

            TextInput::make('capacity')
                ->label('Kapasitas Penumpang')
                ->numeric()
                ->minValue(0)
                ->required(),

            TextInput::make('driver_name')
                ->label('Nama Sopir')
                ->maxLength(100),

            TextInput::make('driver_contact')
                ->label('Kontak Sopir')
                ->maxLength(100),

            Select::make('status')
                ->label('Status')
                ->options(collect(VehicleStatus::cases())->mapWithKeys(fn($s) => [$s->value => $s->label()]))
                ->default(VehicleStatus::Active->value)
                ->required(),
        ]);
    }
}
