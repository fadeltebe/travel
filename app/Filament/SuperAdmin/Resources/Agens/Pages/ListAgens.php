<?php

namespace App\Filament\Owner\Resources\Agens\Pages;

use App\Filament\Owner\Resources\Agens\AgenResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAgens extends ListRecords
{
    protected static string $resource = AgenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
