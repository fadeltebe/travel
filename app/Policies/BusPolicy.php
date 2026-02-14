<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Bus;
use Illuminate\Auth\Access\HandlesAuthorization;

class BusPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Bus');
    }

    public function view(AuthUser $authUser, Bus $bus): bool
    {
        return $authUser->can('View:Bus');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Bus');
    }

    public function update(AuthUser $authUser, Bus $bus): bool
    {
        return $authUser->can('Update:Bus');
    }

    public function delete(AuthUser $authUser, Bus $bus): bool
    {
        return $authUser->can('Delete:Bus');
    }

    public function restore(AuthUser $authUser, Bus $bus): bool
    {
        return $authUser->can('Restore:Bus');
    }

    public function forceDelete(AuthUser $authUser, Bus $bus): bool
    {
        return $authUser->can('ForceDelete:Bus');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Bus');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Bus');
    }

    public function replicate(AuthUser $authUser, Bus $bus): bool
    {
        return $authUser->can('Replicate:Bus');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Bus');
    }

}