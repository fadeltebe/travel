<?php

namespace App\Filament\Superadmin\Resources\SeatBookings\Pages;

use App\Filament\Superadmin\Resources\SeatBookings\SeatBookingResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditSeatBooking extends EditRecord
{
    protected static string $resource = SeatBookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
