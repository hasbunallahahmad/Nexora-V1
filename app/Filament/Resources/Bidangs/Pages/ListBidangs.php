<?php

namespace App\Filament\Resources\Bidangs\Pages;

use App\Filament\Resources\Bidangs\BidangResource;
use App\Models\Bidang;
use App\Services\BidangService;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

class ListBidangs extends ListRecords
{
    protected static string $resource = BidangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Bidang')
                ->icon('heroicon-o-plus-circle')
                ->modalHeading('Tambah Bidang Baru')
                ->modalWidth('lg')
                ->createAnother(false)
                ->schema([
                    TextInput::make('nama_bidang')
                        ->label('Nama Bidang')
                        ->prefixIcon(Heroicon::BuildingLibrary)
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($set, $state) {
                            if ($state) {
                                $set('slug', Str::slug($state));
                            }
                        }),
                    TextInput::make('slug')
                        ->label('Slug')
                        ->required()
                        ->prefixIcon(Heroicon::Link)
                        ->helperText('Auto-generated dari nama bidang'),
                ])
                ->action(function (array $data, BidangService $service) {
                    $bidang = $service->create($data);

                    Notification::make()
                        ->title('Berhasil!')
                        ->body("Bidang {$bidang->nama_bidang} berhasil ditambahkan.")
                        ->success()
                        ->duration(3000)
                        ->send();
                }),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            EditAction::make()
                ->label('Edit')
                ->icon('heroicon-o-pencil-square')
                ->color('warning')
                ->modalHeading('Edit Bidang')
                ->modalWidth('lg')
                ->schema([
                    TextInput::make('nama_bidang')
                        ->label('Nama Bidang')
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($set, $state) {
                            if ($state) {
                                $set('slug', Str::slug($state));
                            }
                        }),
                    TextInput::make('slug')
                        ->label('Slug')
                        ->required()
                        ->prefix(url('/bidang/'))
                        ->helperText('Auto-generated dari nama bidang'),
                ])
                ->action(function (array $data, Bidang $record, BidangService $service) {
                    $bidang = $service->update($record->id, $data);

                    Notification::make()
                        ->title('Berhasil!')
                        ->body("Bidang {$bidang->nama_bidang} berhasil diperbarui.")
                        ->success()
                        ->duration(3000)
                        ->send();
                }),

            DeleteAction::make()
                ->label('Hapus')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Hapus Bidang')
                ->modalDescription('Apakah Anda yakin ingin menghapus bidang ini?')
                ->action(function (Bidang $record) {
                    $record->delete();

                    Notification::make()
                        ->title('Berhasil!')
                        ->body('Bidang berhasil dihapus.')
                        ->success()
                        ->duration(3000)
                        ->send();
                }),
        ];
    }
}
