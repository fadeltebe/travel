<?php

namespace App\Filament\Resources\BusLayouts\Pages;

use App\Filament\Resources\BusLayouts\BusLayoutResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBusLayouts extends ListRecords
{
    protected static string $resource = BusLayoutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
