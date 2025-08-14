<?php

namespace App\Filament\Owner\Resources\Jadwals\Pages;

use App\Filament\Owner\Resources\Jadwals\JadwalResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListJadwals extends ListRecords
{
    protected static string $resource = JadwalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
