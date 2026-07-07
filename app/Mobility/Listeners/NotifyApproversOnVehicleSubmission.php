<?php

declare(strict_types=1);

namespace App\Mobility\Listeners;

use App\Mobility\Events\VehicleReservationSubmitted;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Support\Facades\Route;

final class NotifyApproversOnVehicleSubmission
{
    private const APPROVAL_PERMISSION = 'Approve:VehicleReservation';

    public function handle(VehicleReservationSubmitted $event): void
    {
        $reservation = $event->reservation->loadMissing(['vehicle', 'requestedBy']);

        $approvers = $this->resolveApprovers();

        if ($approvers->isEmpty()) {
            return;
        }

        $requesterLabel = $reservation->requestedBy?->name ?? 'Pengguna Tidak Dikenal';

        $notification = FilamentNotification::make()
            ->title('Reservasi kendaraan baru menunggu persetujuan')
            ->body("{$reservation->title} — {$reservation->vehicle?->name} oleh {$requesterLabel}")
            ->icon('heroicon-o-truck')
            ->iconColor('warning');

        // Route Filament Resource untuk VehicleReservation baru ada di
        // Fase 5 — sebelum itu, notifikasi tetap terkirim tanpa tombol
        // aksi, bukan gagal total.
        if (Route::has('filament.admin.resources.vehicle-reservations.index')) {
            $notification->actions([
                Action::make('view')
                    ->label('Lihat Reservasi')
                    ->url(route('filament.admin.resources.vehicle-reservations.index'))
                    ->markAsRead(),
            ]);
        }

        $notification->sendToDatabase($approvers);
    }

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
