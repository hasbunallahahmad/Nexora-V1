<?php

namespace App\Filament\Resources\Agendas\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

class AgendaForm
{
    public static function getComponents(): array
    {
        return [
            TextInput::make('judul_agenda')
                ->label('Judul Agenda')
                ->required()
                ->minLength(5)
                ->maxLength(100)
                ->regex('/^[a-zA-Z0-9\s\&\-\.\,\(\)]+$/u')
                ->live(onBlur: true)
                ->afterStateUpdated(function ($set, $state) {
                    if ($state) {
                        $set('slug', Str::slug($state));
                    }
                })
                ->suffixIcon(Heroicon::DocumentText)
                ->placeholder('Masukkan judul agenda')
                ->helperText('Minimal 5, maksimal 100 karakter')
                ->columnSpanFull(),

            Hidden::make('slug')
                ->label('Slug')
                ->live(onBlur: true)
                ->unique(ignoreRecord: true)
                ->dehydrated(false)
                ->disabled(),

            Textarea::make('deskripsi')
                ->label('Deskripsi')
                ->maxLength(150)
                ->placeholder('Masukkan deskripsi agenda')
                ->helperText('Maksimal 150 karakter')
                ->columnSpanFull(),

            TextInput::make('location')
                ->label('Lokasi')
                ->required()
                ->minLength(3)
                ->maxLength(100)
                ->regex('/^[a-zA-Z0-9\s\&\-\.\,\(\)]+$/u')
                ->suffixIcon(Heroicon::MapPin)
                ->placeholder('Masukkan lokasi agenda')
                ->helperText('Minimal 3, maksimal 100 karakter')
                ->columnSpan(1),

            DateTimePicker::make('start_date')
                ->label('Tanggal Mulai')
                ->required()
                ->timezone('Asia/Jakarta')
                ->seconds(false)
                ->suffixIcon(Heroicon::CalendarDays)
                ->placeholder('Pilih tanggal mulai')
                // ->minDate(now())
                ->columnSpan(1),

            DateTimePicker::make('end_date')
                ->label('Tanggal Selesai')
                ->timezone('Asia/Jakarta')
                ->seconds(false)
                ->suffixIcon(Heroicon::CalendarDays)
                ->placeholder('Pilih tanggal selesai')
                ->afterOrEqual('start_date')
                ->columnSpan(1),

            Select::make('bidang_id')
                ->label('Bidang')
                ->relationship('bidang', 'nama_bidang', fn($query) => $query->orderBy('nama_bidang'))
                ->searchable()
                ->multiple()
                ->preload()
                ->searchDebounce(500)
                ->loadingMessage('Loading nama bidang...')
                ->noSearchResultsMessage('Tidak ditemukan Bidang yang anda cari.')
                ->noOptionsMessage('Bidang tidak ada.')
                ->placeholder('Pilih bidang terkait')
                ->columnSpanFull(),

            Toggle::make('is_published')
                ->label('Status')
                ->default(false)
                ->onColor('success')
                ->offColor('danger')
                ->inline()
                ->onIcon(Heroicon::Check)
                ->offIcon(Heroicon::XMark)
                ->helperText('Aktifkan untuk mempublikasikan agenda, nonaktifkan untuk menyembunyikan agenda dari publik.')
                ->columnSpanFull(),
        ];
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema->components(static::getComponents());
    }
}
