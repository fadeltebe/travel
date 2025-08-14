<?php

namespace App\Filament\Admin\Resources\Rutes\Pages;

use App\Filament\Admin\Resources\Rutes\RuteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRutes extends ListRecords
{
    protected static string $resource = RuteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
