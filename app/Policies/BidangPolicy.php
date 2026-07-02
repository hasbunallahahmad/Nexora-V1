<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Bidang;
use Illuminate\Auth\Access\HandlesAuthorization;

class BidangPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Bidang');
    }

    public function view(AuthUser $authUser, Bidang $bidang): bool
    {
        return $authUser->can('View:Bidang');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Bidang');
    }

    public function update(AuthUser $authUser, Bidang $bidang): bool
    {
        return $authUser->can('Update:Bidang');
    }

    public function delete(AuthUser $authUser, Bidang $bidang): bool
    {
        return $authUser->can('Delete:Bidang');
    }

    public function restore(AuthUser $authUser, Bidang $bidang): bool
    {
        return $authUser->can('Restore:Bidang');
    }

    public function forceDelete(AuthUser $authUser, Bidang $bidang): bool
    {
        return $authUser->can('ForceDelete:Bidang');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Bidang');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Bidang');
    }

    public function replicate(AuthUser $authUser, Bidang $bidang): bool
    {
        return $authUser->can('Replicate:Bidang');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Bidang');
    }

}