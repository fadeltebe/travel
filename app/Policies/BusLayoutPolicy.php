<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\BusLayout;
use Illuminate\Auth\Access\HandlesAuthorization;

class BusLayoutPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:BusLayout');
    }

    public function view(AuthUser $authUser, BusLayout $busLayout): bool
    {
        return $authUser->can('View:BusLayout');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:BusLayout');
    }

    public function update(AuthUser $authUser, BusLayout $busLayout): bool
    {
        return $authUser->can('Update:BusLayout');
    }

    public function delete(AuthUser $authUser, BusLayout $busLayout): bool
    {
        return $authUser->can('Delete:BusLayout');
    }

    public function restore(AuthUser $authUser, BusLayout $busLayout): bool
    {
        return $authUser->can('Restore:BusLayout');
    }

    public function forceDelete(AuthUser $authUser, BusLayout $busLayout): bool
    {
        return $authUser->can('ForceDelete:BusLayout');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:BusLayout');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:BusLayout');
    }

    public function replicate(AuthUser $authUser, BusLayout $busLayout): bool
    {
        return $authUser->can('Replicate:BusLayout');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:BusLayout');
    }

}