<?php

declare(strict_types=1);

namespace App\Mobility\Filament\Resources\VehicleReservations\Schemas;

use App\Mobility\Models\Vehicle;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class VehicleReservationForm
{
    public static function getComponents(): array
    {
        return [
            Select::make('vehicle_id')
                ->label('Kendaraan')
                ->options(Vehicle::query()->pluck('name', 'id'))
                ->searchable()
                ->required(),

            Select::make('agenda_id')
                ->label('Agenda Terkait (opsional)')
                ->relationship('agenda', 'judul_agenda')
                ->searchable()
                ->nullable(),

            TextInput::make('title')
                ->label('Judul Keperluan')
                ->required()
                ->maxLength(150),

            TextInput::make('destination')
                ->label('Tujuan')
                ->maxLength(150)
                ->placeholder('Kota/lokasi tujuan (opsional untuk dalam kota)'),

            Textarea::make('purpose')
                ->label('Keterangan')
                ->maxLength(255),

            DateTimePicker::make('start_datetime')
                ->label('Mulai')
                ->required()
                ->seconds(false),

            DateTimePicker::make('end_datetime')
                ->label('Selesai')
                ->required()
                ->seconds(false)
                ->afterOrEqual('start_datetime'),
        ];
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema->components(self::getComponents());
    }
}
