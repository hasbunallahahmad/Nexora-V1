<?php

declare(strict_types=1);

namespace App\Mobility\Filament\Resources\Vehicles\Tables;

use App\Mobility\Enums\VehicleStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class VehiclesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(\App\Mobility\Models\Vehicle::query()->withCount('occupyingReservations'))
            ->heading('Daftar Kendaraan')
            ->description(
                'Tabel ini menampilkan daftar kendaraan yang terdaftar dalam sistem.'
            )
            ->deferLoading()
            ->paginated(['10', '25', '50'])
            ->columns([
                TextColumn::make('name')->label('Nama')->searchable()->sortable(),
                TextColumn::make('plate_number')->label('Nomor Polisi')->searchable(),
                TextColumn::make('type')->label('Jenis'),
                TextColumn::make('capacity')->label('Kapasitas')->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn(VehicleStatus $state) => $state->label())
                    ->color(fn(VehicleStatus $state) => match ($state) {
                        VehicleStatus::Active => 'success',
                        VehicleStatus::Inactive => 'gray',
                    }),
                TextColumn::make('occupying_reservations_count')
                    ->label('Reservasi Aktif')
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(collect(VehicleStatus::cases())->mapWithKeys(fn($s) => [$s->value => $s->label()])),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                CreateAction::make(),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
