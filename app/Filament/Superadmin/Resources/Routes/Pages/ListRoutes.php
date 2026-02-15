<?php

namespace App\Filament\Superadmin\Resources\Routes\Pages;

use App\Filament\Superadmin\Resources\Routes\RouteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRoutes extends ListRecords
{
    protected static string $resource = RouteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
