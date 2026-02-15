<?php

namespace App\Filament\Superadmin\Resources\BusLayouts\Pages;

use App\Filament\Superadmin\Resources\BusLayouts\BusLayoutResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditBusLayout extends EditRecord
{
    protected static string $resource = BusLayoutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
