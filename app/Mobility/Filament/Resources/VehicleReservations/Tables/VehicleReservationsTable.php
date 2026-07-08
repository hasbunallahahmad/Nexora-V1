<?php

declare(strict_types=1);

namespace App\Mobility\Filament\Resources\VehicleReservations\Tables;

use App\Mobility\DTO\ApproveVehicleReservationData;
use App\Mobility\DTO\RejectVehicleReservationData;
use App\Mobility\Exceptions\InvalidVehicleReservationTransitionException;
use App\Mobility\Exceptions\VehicleMaintenanceConflictException;
use App\Mobility\Exceptions\VehicleNotReservableException;
use App\Mobility\Exceptions\VehicleReservationConflictException;
use App\Mobility\Models\VehicleReservation;
use App\Mobility\Services\VehicleReservationService;
use App\Shared\Enums\ReservationStatus;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class VehicleReservationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(
                VehicleReservation::query()->with(['vehicle', 'requestedBy', 'approvedBy', 'agenda'])
            )
            ->deferLoading()
            ->paginated(['10', '25', '50'])
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('title')->label('Judul')->searchable()->wrap(),
                TextColumn::make('vehicle.name')->label('Kendaraan')->sortable(),
                TextColumn::make('vehicle.driver_name')->label('Sopir')->placeholder('—'),
                TextColumn::make('destination')->label('Tujuan')->placeholder('—'),
                TextColumn::make('requestedBy.name')->label('Diajukan Oleh'),
                TextColumn::make('start_datetime')->label('Mulai')->dateTime('d M Y H:i')->sortable(),
                TextColumn::make('end_datetime')->label('Selesai')->dateTime('d M Y H:i')->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn(ReservationStatus $state) => $state->label())
                    ->color(fn(ReservationStatus $state) => match ($state) {
                        ReservationStatus::Draft => 'gray',
                        ReservationStatus::Submitted => 'warning',
                        ReservationStatus::Approved => 'success',
                        ReservationStatus::Rejected => 'danger',
                        ReservationStatus::Cancelled => 'gray',
                        ReservationStatus::Completed => 'info',
                    }),
                TextColumn::make('approvedBy.name')->label('Disetujui Oleh')->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(collect(ReservationStatus::cases())->mapWithKeys(fn($s) => [$s->value => $s->label()])),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn(VehicleReservation $record) => $record->status === ReservationStatus::Draft),

                Action::make('submit')
                    ->label('Ajukan')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->visible(fn(VehicleReservation $record) => $record->status === ReservationStatus::Draft
                        && Auth::user()->can('update', $record))
                    ->requiresConfirmation()
                    ->action(function (VehicleReservation $record, VehicleReservationService $service): void {
                        try {
                            $service->submit($record->id);

                            Notification::make()->title('Reservasi berhasil diajukan')->success()->send();
                        } catch (VehicleReservationConflictException | VehicleMaintenanceConflictException | VehicleNotReservableException | InvalidVehicleReservationTransitionException $e) {
                            Notification::make()->title('Gagal mengajukan reservasi')->body($e->getMessage())->danger()->send();
                        }
                    }),

                Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn(VehicleReservation $record) => Auth::user()->can('approve', $record)
                        && $record->status === ReservationStatus::Submitted)
                    ->requiresConfirmation()
                    ->action(function (VehicleReservation $record, VehicleReservationService $service): void {
                        try {
                            $service->approve(new ApproveVehicleReservationData($record->id, Auth::id()));

                            Notification::make()->title('Reservasi disetujui')->success()->send();
                        } catch (VehicleReservationConflictException | VehicleMaintenanceConflictException | InvalidVehicleReservationTransitionException $e) {
                            Notification::make()->title('Gagal menyetujui')->body($e->getMessage())->danger()->send();
                        }
                    }),

                Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn(VehicleReservation $record) => Auth::user()->can('reject', $record)
                        && $record->status === ReservationStatus::Submitted)
                    ->requiresConfirmation()
                    ->schema([
                        Textarea::make('reason')
                            ->label('Alasan Penolakan')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->action(function (VehicleReservation $record, array $data, VehicleReservationService $service): void {
                        try {
                            $service->reject(new RejectVehicleReservationData($record->id, Auth::id(), $data['reason']));

                            Notification::make()->title('Reservasi ditolak')->success()->send();
                        } catch (InvalidVehicleReservationTransitionException $e) {
                            Notification::make()->title('Gagal menolak')->body($e->getMessage())->danger()->send();
                        }
                    }),

                Action::make('cancel')
                    ->label('Batalkan')
                    ->icon('heroicon-o-no-symbol')
                    ->color('gray')
                    ->visible(fn(VehicleReservation $record) => Auth::user()->can('cancel', $record)
                        && in_array($record->status, [ReservationStatus::Draft, ReservationStatus::Submitted, ReservationStatus::Approved], true))
                    ->requiresConfirmation()
                    ->action(function (VehicleReservation $record, VehicleReservationService $service): void {
                        try {
                            $service->cancel($record->id);

                            Notification::make()->title('Reservasi dibatalkan')->success()->send();
                        } catch (InvalidVehicleReservationTransitionException $e) {
                            Notification::make()->title('Gagal membatalkan')->body($e->getMessage())->danger()->send();
                        }
                    }),

                DeleteAction::make()
                    ->visible(fn(VehicleReservation $record) => Auth::user()->can('delete', $record)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
