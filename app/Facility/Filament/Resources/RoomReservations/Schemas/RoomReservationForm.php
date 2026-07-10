<?php

declare(strict_types=1);

namespace App\Facility\Filament\Resources\RoomReservations\Schemas;

use App\Facility\Models\Room;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RoomReservationForm
{
    public static function getComponents(): array
    {
        return [
            Select::make('room_id')
                ->label('Ruangan')
                ->options(Room::query()->pluck('name', 'id'))
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

            Textarea::make('purpose')
                ->label('Keterangan')
                ->maxLength(255),

            TextInput::make('guest_contact')
                ->label('Nomor Kontak Tamu')
                ->numeric()
                ->tel()
                ->required()
                ->maxLength(15),

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
