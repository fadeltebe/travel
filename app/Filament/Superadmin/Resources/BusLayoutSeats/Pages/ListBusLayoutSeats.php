<?php

namespace App\Filament\Superadmin\Resources\BusLayoutSeats\Pages;

use App\Filament\Superadmin\Resources\BusLayoutSeats\BusLayoutSeatResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBusLayoutSeats extends ListRecords
{
    protected static string $resource = BusLayoutSeatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
