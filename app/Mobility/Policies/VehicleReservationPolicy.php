<?php

declare(strict_types=1);

namespace App\Mobility\Policies;

use App\Mobility\Models\VehicleReservation;
use App\Shared\Enums\ReservationStatus;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class VehicleReservationPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:VehicleReservation');
    }

    public function view(AuthUser $authUser, VehicleReservation $reservation): bool
    {
        return $authUser->can('View:VehicleReservation')
            || $this->isOwner($authUser, $reservation);
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:VehicleReservation');
    }

    public function update(AuthUser $authUser, VehicleReservation $reservation): bool
    {
        if ($authUser->can('Update:VehicleReservation')) {
            return true;
        }

        return $this->isOwner($authUser, $reservation)
            && in_array($reservation->status, [ReservationStatus::Draft, ReservationStatus::Submitted], true);
    }

    public function cancel(AuthUser $authUser, VehicleReservation $reservation): bool
    {
        if ($authUser->can('Cancel:VehicleReservation')) {
            return true;
        }

        return $this->isOwner($authUser, $reservation);
    }

    /**
     * Approve/Reject BUKAN ability CRUD standar — permission ini
     * didaftarkan & di-assign manual lewat MobilityPermissionSeeder.
     */
    public function approve(AuthUser $authUser, VehicleReservation $reservation): bool
    {
        return $authUser->can('Approve:VehicleReservation');
    }

    public function reject(AuthUser $authUser, VehicleReservation $reservation): bool
    {
        return $authUser->can('Reject:VehicleReservation');
    }

    public function delete(AuthUser $authUser, VehicleReservation $reservation): bool
    {
        if (! $authUser->can('Delete:VehicleReservation')) {
            return false;
        }

        // Lindungi histori — Approved/Submitted/Completed tidak boleh
        // dihapus permanen meski user punya permission, konsisten
        // dengan RoomReservationPolicy.
        return in_array($reservation->status, [ReservationStatus::Draft, ReservationStatus::Cancelled], true);
    }

    public function restore(AuthUser $authUser, VehicleReservation $reservation): bool
    {
        return $authUser->can('Restore:VehicleReservation');
    }

    public function forceDelete(AuthUser $authUser, VehicleReservation $reservation): bool
    {
        return $authUser->can('ForceDelete:VehicleReservation');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:VehicleReservation');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:VehicleReservation');
    }

    public function replicate(AuthUser $authUser, VehicleReservation $reservation): bool
    {
        return $authUser->can('Replicate:VehicleReservation');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:VehicleReservation');
    }

    private function isOwner(AuthUser $authUser, VehicleReservation $reservation): bool
    {
        return $reservation->requested_by === $authUser->getKey();
    }
}
