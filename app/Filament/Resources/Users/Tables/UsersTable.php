<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(User::query()->with('roles'))
            ->deferLoading()
            ->paginated(['10', '25', '50'])
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')->label('Nama')->searchable()->sortable(),
                TextColumn::make('email')->label('Email')->searchable()->sortable(),
                TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge()
                    ->separator(','),
                IconColumn::make('is_active')->label('Aktif')->boolean(),
                IconColumn::make('must_change_password')
                    ->label('Perlu Ganti Password')
                    ->boolean()
                    ->trueColor('warning'),
                TextColumn::make('created_at')->label('Dibuat')->dateTime('d M Y H:i'),
            ])
            // ->filters([
            //     //
            // ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->visible(fn(User $record) => $record->id !== Auth::id()),
            ]);
        // ->toolbarActions([
        //     BulkActionGroup::make([
        //         DeleteBulkAction::make(),
        //     ]),
        // ]);
    }
}
