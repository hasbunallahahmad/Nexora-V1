<?php

declare(strict_types=1);

namespace App\Facility\Filament\Resources\RoomReservations\Tables;

use App\Facility\DTO\ApproveReservationData;
use App\Facility\DTO\RejectReservationData;
use App\Facility\Enums\ReservationStatus;
use App\Facility\Exceptions\InvalidReservationTransitionException;
use App\Facility\Exceptions\ReservationConflictException;
use App\Facility\Exceptions\RoomNotReservableException;
use App\Facility\Models\RoomReservation;
use App\Facility\Services\RoomReservationService;
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

class RoomReservationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(
                RoomReservation::query()->with(['room', 'requestedBy', 'approvedBy', 'agenda'])
            )
            ->deferLoading()
            ->paginated(['10', '25', '50'])
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('title')->label('Judul')->searchable()->wrap(),
                TextColumn::make('room.name')->label('Ruangan')->sortable(),
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
                    ->visible(fn(RoomReservation $record) => $record->status === ReservationStatus::Draft),

                Action::make('submit')
                    ->label('Ajukan')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->visible(fn(RoomReservation $record) => $record->status === ReservationStatus::Draft
                        && Auth::user()->can('update', $record))
                    ->requiresConfirmation()
                    ->action(function (RoomReservation $record, RoomReservationService $service): void {
                        try {
                            $service->submit($record->id);

                            Notification::make()->title('Reservasi berhasil diajukan')->success()->send();
                        } catch (ReservationConflictException | RoomNotReservableException | InvalidReservationTransitionException $e) {
                            Notification::make()->title('Gagal mengajukan reservasi')->body($e->getMessage())->danger()->send();
                        }
                    }),

                Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn(RoomReservation $record) => Auth::user()->can('approve', $record)
                        && $record->status === ReservationStatus::Submitted)
                    ->requiresConfirmation()
                    ->action(function (RoomReservation $record, RoomReservationService $service): void {
                        try {
                            $service->approve(new ApproveReservationData($record->id, Auth::id()));

                            Notification::make()->title('Reservasi disetujui')->success()->send();
                        } catch (ReservationConflictException | InvalidReservationTransitionException $e) {
                            Notification::make()->title('Gagal menyetujui')->body($e->getMessage())->danger()->send();
                        }
                    }),

                Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn(RoomReservation $record) => Auth::user()->can('reject', $record)
                        && $record->status === ReservationStatus::Submitted)
                    ->requiresConfirmation()
                    ->schema([
                        Textarea::make('reason')
                            ->label('Alasan Penolakan')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->action(function (RoomReservation $record, array $data, RoomReservationService $service): void {
                        try {
                            $service->reject(new RejectReservationData($record->id, Auth::id(), $data['reason']));

                            Notification::make()->title('Reservasi ditolak')->success()->send();
                        } catch (InvalidReservationTransitionException $e) {
                            Notification::make()->title('Gagal menolak')->body($e->getMessage())->danger()->send();
                        }
                    }),

                Action::make('cancel')
                    ->label('Batalkan')
                    ->icon('heroicon-o-no-symbol')
                    ->color('gray')
                    ->visible(fn(RoomReservation $record) => Auth::user()->can('cancel', $record)
                        && in_array($record->status, [ReservationStatus::Draft, ReservationStatus::Submitted, ReservationStatus::Approved], true))
                    ->requiresConfirmation()
                    ->action(function (RoomReservation $record, RoomReservationService $service): void {
                        try {
                            $service->cancel($record->id);

                            Notification::make()->title('Reservasi dibatalkan')->success()->send();
                        } catch (InvalidReservationTransitionException $e) {
                            Notification::make()->title('Gagal membatalkan')->body($e->getMessage())->danger()->send();
                        }
                    }),

                DeleteAction::make()
                    ->visible(fn(RoomReservation $record) => Auth::user()->can('delete', $record)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
