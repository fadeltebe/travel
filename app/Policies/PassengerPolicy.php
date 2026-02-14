<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Passenger;
use Illuminate\Auth\Access\HandlesAuthorization;

class PassengerPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Passenger');
    }

    public function view(AuthUser $authUser, Passenger $passenger): bool
    {
        return $authUser->can('View:Passenger');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Passenger');
    }

    public function update(AuthUser $authUser, Passenger $passenger): bool
    {
        return $authUser->can('Update:Passenger');
    }

    public function delete(AuthUser $authUser, Passenger $passenger): bool
    {
        return $authUser->can('Delete:Passenger');
    }

    public function restore(AuthUser $authUser, Passenger $passenger): bool
    {
        return $authUser->can('Restore:Passenger');
    }

    public function forceDelete(AuthUser $authUser, Passenger $passenger): bool
    {
        return $authUser->can('ForceDelete:Passenger');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Passenger');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Passenger');
    }

    public function replicate(AuthUser $authUser, Passenger $passenger): bool
    {
        return $authUser->can('Replicate:Passenger');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Passenger');
    }

}