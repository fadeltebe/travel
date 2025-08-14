<?php

namespace App\Filament\Owner\Resources\Travel\Pages;

use App\Filament\Owner\Resources\Travel\TravelResource;
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
