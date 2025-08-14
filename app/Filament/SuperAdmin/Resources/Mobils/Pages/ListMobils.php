<?php

namespace App\Filament\Admin\Resources\Mobils\Pages;

use App\Filament\Admin\Resources\Mobils\MobilResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMobils extends ListRecords
{
    protected static string $resource = MobilResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
