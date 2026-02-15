<?php

namespace App\Filament\Superadmin\Resources\CargoReceipts\Pages;

use App\Filament\Superadmin\Resources\CargoReceipts\CargoReceiptResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditCargoReceipt extends EditRecord
{
    protected static string $resource = CargoReceiptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
