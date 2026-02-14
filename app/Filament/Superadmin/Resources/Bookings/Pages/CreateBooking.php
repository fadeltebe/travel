<?php

namespace App\Filament\Superadmin\Resources\Bookings\Pages;

use App\Filament\Superadmin\Resources\Bookings\BookingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBooking extends CreateRecord
{
    protected static string $resource = BookingResource::class;
}
