<?php

namespace App\Filament\Resources\BusLayouts\Pages;

use App\Filament\Resources\BusLayouts\BusLayoutResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewBusLayout extends ViewRecord
{
    protected static string $resource = BusLayoutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
