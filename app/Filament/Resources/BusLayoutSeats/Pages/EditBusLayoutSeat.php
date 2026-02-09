<?php

namespace App\Filament\Resources\BusLayoutSeats\Pages;

use App\Filament\Resources\BusLayoutSeats\BusLayoutSeatResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditBusLayoutSeat extends EditRecord
{
    protected static string $resource = BusLayoutSeatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
