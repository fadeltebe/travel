<?php

namespace App\Filament\SuperAdmin\Resources\Agens\Pages;

use App\Filament\SuperAdmin\Resources\Agens\AgenResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAgen extends ViewRecord
{
    protected static string $resource = AgenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
