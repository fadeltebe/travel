<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function update(User $user, Booking $booking)
    {
        // Hanya bisa update jika dia SuperAdmin atau dia adalah Agen Asal
        return $user->canViewAll() || $booking->isOriginAgent();
    }
}
