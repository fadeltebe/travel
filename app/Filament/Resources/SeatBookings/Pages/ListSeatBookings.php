<?php

namespace App\Filament\Resources\SeatBookings\Pages;

use App\Filament\Resources\SeatBookings\SeatBookingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSeatBookings extends ListRecords
{
    protected static string $resource = SeatBookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
