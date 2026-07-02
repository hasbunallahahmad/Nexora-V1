<?php

namespace App\Filament\Resources\Bidangs\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class BidangForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_bidang')
                    ->label('Nama Bidang')
                    ->required()
                    ->minLength(3)
                    ->maxLength(30)
                    ->regex('/^[a-zA-Z\s\&\-\.\,]+$/')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($set, $state) {
                        if ($state) {
                            $set('slug', Str::slug($state));
                        }
                    })
                    ->suffixIcon('heroicon-o-building-library')
                    ->placeholder('Masukkan nama bidang')
                    ->helperText('Minimal 3, maksimal 30 karakter. Hanya huruf, spasi, dan simbol (&-.)'),

                TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->maxLength(25)
                    ->alphaNumericDash()
                    ->suffixIcon('heroicon-o-link')
                    ->helperText('Auto-generated dari nama bidang. Hanya huruf, angka, dash, dan underscore'),
            ]);
    }
}
