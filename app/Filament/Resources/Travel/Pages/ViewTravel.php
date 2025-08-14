<?php

namespace App\Filament\Resources\Travel\Pages;

use App\Filament\Resources\Travel\TravelResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTravel extends ViewRecord
{
    protected static string $resource = TravelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
