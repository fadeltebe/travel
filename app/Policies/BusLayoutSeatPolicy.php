<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\BusLayoutSeat;
use Illuminate\Auth\Access\HandlesAuthorization;

class BusLayoutSeatPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:BusLayoutSeat');
    }

    public function view(AuthUser $authUser, BusLayoutSeat $busLayoutSeat): bool
    {
        return $authUser->can('View:BusLayoutSeat');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:BusLayoutSeat');
    }

    public function update(AuthUser $authUser, BusLayoutSeat $busLayoutSeat): bool
    {
        return $authUser->can('Update:BusLayoutSeat');
    }

    public function delete(AuthUser $authUser, BusLayoutSeat $busLayoutSeat): bool
    {
        return $authUser->can('Delete:BusLayoutSeat');
    }

    public function restore(AuthUser $authUser, BusLayoutSeat $busLayoutSeat): bool
    {
        return $authUser->can('Restore:BusLayoutSeat');
    }

    public function forceDelete(AuthUser $authUser, BusLayoutSeat $busLayoutSeat): bool
    {
        return $authUser->can('ForceDelete:BusLayoutSeat');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:BusLayoutSeat');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:BusLayoutSeat');
    }

    public function replicate(AuthUser $authUser, BusLayoutSeat $busLayoutSeat): bool
    {
        return $authUser->can('Replicate:BusLayoutSeat');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:BusLayoutSeat');
    }

}