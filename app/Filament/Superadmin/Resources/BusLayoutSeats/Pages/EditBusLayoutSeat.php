<?php

namespace App\Filament\Superadmin\Resources\BusLayoutSeats\Pages;

use App\Filament\Superadmin\Resources\BusLayoutSeats\BusLayoutSeatResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditBusLayoutSeat extends EditRecord
{
    protected static string $resource = BusLayoutSeatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
