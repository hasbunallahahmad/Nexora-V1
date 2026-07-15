<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Role;

class UserForm
{

    public static function getComponents(): array
    {
        return [
            TextInput::make('name')
                ->label('Nama')
                ->required()
                ->maxLength(150),

            TextInput::make('email')
                ->label('Email')
                ->email()
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(150),

            Select::make('roles')
                ->label('Role')
                ->relationship('roles', 'name')
                ->options(Role::query()->pluck('name', 'id'))
                ->searchable()
                ->preload()
                ->required(),

            Toggle::make('is_active')
                ->label('Aktif')
                ->default(true),
        ];
    }
    public static function configure(Schema $schema): Schema
    {
        return $schema->components(self::getComponents());
    }
}
