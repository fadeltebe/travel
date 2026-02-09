<?php

namespace App\Filament\Resources\BusLayoutSeats\Pages;

use App\Filament\Resources\BusLayoutSeats\BusLayoutSeatResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewBusLayoutSeat extends ViewRecord
{
    protected static string $resource = BusLayoutSeatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
