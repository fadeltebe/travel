<?php

namespace App\Filament\Owner\Resources\Travel\Pages;

use App\Filament\Owner\Resources\Travel\TravelResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTravel extends ListRecords
{
    protected static string $resource = TravelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
