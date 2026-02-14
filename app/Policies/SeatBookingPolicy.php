<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\SeatBooking;
use Illuminate\Auth\Access\HandlesAuthorization;

class SeatBookingPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:SeatBooking');
    }

    public function view(AuthUser $authUser, SeatBooking $seatBooking): bool
    {
        return $authUser->can('View:SeatBooking');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:SeatBooking');
    }

    public function update(AuthUser $authUser, SeatBooking $seatBooking): bool
    {
        return $authUser->can('Update:SeatBooking');
    }

    public function delete(AuthUser $authUser, SeatBooking $seatBooking): bool
    {
        return $authUser->can('Delete:SeatBooking');
    }

    public function restore(AuthUser $authUser, SeatBooking $seatBooking): bool
    {
        return $authUser->can('Restore:SeatBooking');
    }

    public function forceDelete(AuthUser $authUser, SeatBooking $seatBooking): bool
    {
        return $authUser->can('ForceDelete:SeatBooking');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:SeatBooking');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:SeatBooking');
    }

    public function replicate(AuthUser $authUser, SeatBooking $seatBooking): bool
    {
        return $authUser->can('Replicate:SeatBooking');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:SeatBooking');
    }

}