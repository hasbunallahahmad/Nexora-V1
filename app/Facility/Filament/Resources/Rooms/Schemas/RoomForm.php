<?php

declare(strict_types=1);

namespace App\Facility\Filament\Resources\Rooms\Schemas;

use App\Facility\Enums\RoomStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RoomForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nama Ruangan')
                ->required()
                ->maxLength(100),

            TextInput::make('location')
                ->label('Lokasi')
                ->maxLength(150),

            TextInput::make('capacity')
                ->label('Kapasitas')
                ->numeric()
                ->minValue(0)
                ->required(),

            Select::make('status')
                ->label('Status')
                ->options(collect(RoomStatus::cases())->mapWithKeys(fn($s) => [$s->value => $s->label()]))
                ->default(RoomStatus::Active->value)
                ->required(),
        ]);
    }
}
