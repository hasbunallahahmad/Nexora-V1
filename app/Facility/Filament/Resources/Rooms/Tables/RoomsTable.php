<?php

declare(strict_types=1);

namespace App\Facility\Filament\Resources\Rooms\Tables;

use App\Facility\Enums\RoomStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RoomsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(\App\Facility\Models\Room::query()->withCount('occupyingReservations'))
            ->heading('Daftar Ruangan')
            ->description(
                'Tabel ini menampilkan daftar ruangan yang terdaftar dalam sistem.'
            )
            ->deferLoading()
            ->paginated(['10', '25', '50'])
            ->columns([
                TextColumn::make('name')->label('Nama')->searchable()->sortable(),
                TextColumn::make('location')->label('Lokasi')->searchable(),
                TextColumn::make('capacity')->label('Kapasitas')->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn(RoomStatus $state) => $state->label())
                    ->color(fn(RoomStatus $state) => match ($state) {
                        RoomStatus::Active => 'success',
                        RoomStatus::Maintenance => 'warning',
                        RoomStatus::Inactive => 'gray',
                    }),
                TextColumn::make('occupying_reservations_count')
                    ->label('Reservasi Aktif')
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(collect(RoomStatus::cases())->mapWithKeys(fn($s) => [$s->value => $s->label()])),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                // CreateAction::make(),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
