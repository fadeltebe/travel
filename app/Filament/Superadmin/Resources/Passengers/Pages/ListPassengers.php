<?php

namespace App\Filament\Superadmin\Resources\Passengers\Pages;

use App\Filament\Superadmin\Resources\Passengers\PassengerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPassengers extends ListRecords
{
    protected static string $resource = PassengerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
