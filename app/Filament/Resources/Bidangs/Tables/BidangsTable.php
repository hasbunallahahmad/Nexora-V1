<?php

namespace App\Filament\Resources\Bidangs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class BidangsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->heading('Daftar Bidang Terdaftar')
            ->description(
                'Tabel ini menampilkan daftar bidang yang terdaftar dalam sistem.'
            )
            ->deferLoading()
            ->paginated('5, 10, 25, 50, 100')
            ->defaultSort('created_at', 'ascs')
            ->poll('10s')
            ->columns([
                TextColumn::make('nama_bidang')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug'),
                TextColumn::make('created_at')
                    ->dateTime('d M Y H:i')
                    ->label('Created At'),
                TextColumn::make('updated_at')
                    ->dateTime('d M Y H:i')
                    ->label('Updated At'),
                TextColumn::make('deleted_at')
                    ->dateTime('d M Y H:i')
                    ->label('Deleted At')
                    ->hidden(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make()
                    ->label(''),
                DeleteAction::make()
                    ->label(''),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
