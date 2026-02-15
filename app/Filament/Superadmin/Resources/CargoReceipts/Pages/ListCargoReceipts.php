<?php

namespace App\Filament\Superadmin\Resources\CargoReceipts\Pages;

use App\Filament\Superadmin\Resources\CargoReceipts\CargoReceiptResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCargoReceipts extends ListRecords
{
    protected static string $resource = CargoReceiptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
