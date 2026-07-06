<?php

declare(strict_types=1);

namespace App\Facility\Policies;

use App\Facility\Enums\ReservationStatus;
use App\Facility\Models\RoomReservation;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class RoomReservationPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:RoomReservation');
    }

    public function view(AuthUser $authUser, RoomReservation $reservation): bool
    {
        return $authUser->can('View:RoomReservation')
            || $this->isOwner($authUser, $reservation);
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:RoomReservation');
    }

    /**
     * Update hanya untuk owner selama status masih Draft/Submitted,
     * atau admin dengan permission penuh kapan saja.
     */
    public function update(AuthUser $authUser, RoomReservation $reservation): bool
    {
        if ($authUser->can('Update:RoomReservation')) {
            return true;
        }

        return $this->isOwner($authUser, $reservation)
            && in_array($reservation->status, [ReservationStatus::Draft, ReservationStatus::Submitted], true);
    }

    /**
     * Cancel: owner boleh membatalkan reservasinya sendiri sebelum mulai,
     * atau admin kapan saja. Logic status-transition tetap divalidasi di
     * Action layer (CancelReservationAction) — policy ini hanya soal "siapa
     * boleh mencoba", bukan "apakah transisinya valid".
     */
    public function cancel(AuthUser $authUser, RoomReservation $reservation): bool
    {
        if ($authUser->can('Cancel:RoomReservation')) {
            return true;
        }

        return $this->isOwner($authUser, $reservation);
    }

    /**
     * Approve/Reject BUKAN ability CRUD standar — permission ini didaftarkan
     * manual (lihat FacilityPermissionSeeder), bukan hasil auto-generate
     * Filament Shield.
     */
    public function approve(AuthUser $authUser, RoomReservation $reservation): bool
    {
        return $authUser->can('Approve:RoomReservation');
    }

    public function reject(AuthUser $authUser, RoomReservation $reservation): bool
    {
        return $authUser->can('Reject:RoomReservation');
    }

    public function delete(AuthUser $authUser, RoomReservation $reservation): bool
    {
        if (! $authUser->can('Delete:RoomReservation')) {
            return false;
        }

        // Admin tetap tidak boleh hapus histori Approved/Completed —
        // hanya Draft/Cancelled yang boleh dihapus permanen.
        return in_array($reservation->status, [ReservationStatus::Draft, ReservationStatus::Cancelled], true);
    }

    public function restore(AuthUser $authUser, RoomReservation $reservation): bool
    {
        return $authUser->can('Restore:RoomReservation');
    }

    public function forceDelete(AuthUser $authUser, RoomReservation $reservation): bool
    {
        return $authUser->can('ForceDelete:RoomReservation');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:RoomReservation');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:RoomReservation');
    }

    public function replicate(AuthUser $authUser, RoomReservation $reservation): bool
    {
        return $authUser->can('Replicate:RoomReservation');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:RoomReservation');
    }

    private function isOwner(AuthUser $authUser, RoomReservation $reservation): bool
    {
        return $reservation->requested_by === $authUser->getKey();
    }
}
