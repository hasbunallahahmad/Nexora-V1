<?php

declare(strict_types=1);

namespace App\Facility\Listeners;

use App\Facility\Events\ReservationSubmitted;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;

final class NotifyApproversOnSubmission
{
    private const APPROVAL_PERMISSION = 'Approve:RoomReservation';

    public function handle(ReservationSubmitted $event): void
    {
        $reservation = $event->reservation->loadMissing('room');

        $approvers = $this->resolveApprovers();

        if ($approvers->isEmpty()) {
            return;
        }

        $requesterLabel = $reservation->requestedBy?->name
            ?? ($reservation->guest_name . ' (' . ($reservation->guest_instansi ?: 'Masyarakat Umum') . ')');

        FilamentNotification::make()
            ->title('Reservasi ruangan baru menunggu persetujuan')
            ->body("{$reservation->title} — {$reservation->room?->name} oleh {$requesterLabel}")
            ->icon('heroicon-o-calendar-days')
            ->iconColor('warning')
            ->actions([
                Action::make('view')
                    ->label('Lihat Reservasi')
                    ->url(route('filament.admin.resources.room-reservations.index'))
                    ->markAsRead(),
            ])
            ->sendToDatabase($approvers);
    }

    /**
     * Query dinamis: siapa pun yang SAAT INI punya permission approve,
     * baik lewat role maupun assignment langsung — bukan hardcode role.
     */
    private function resolveApprovers()
    {
        return User::query()
            ->where(function ($query) {
                $query->whereHas('roles.permissions', function ($q) {
                    $q->where('name', self::APPROVAL_PERMISSION);
                })->orWhereHas('permissions', function ($q) {
                    $q->where('name', self::APPROVAL_PERMISSION);
                });
            })
            ->get();
    }
}
