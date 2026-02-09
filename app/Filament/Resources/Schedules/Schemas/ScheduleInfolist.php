<?php

namespace App\Filament\Resources\Schedules\Schemas;

use App\Models\Schedule;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ScheduleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('route_id')
                    ->numeric(),
                TextEntry::make('bus_id')
                    ->numeric(),
                TextEntry::make('departure_date')
                    ->date(),
                TextEntry::make('departure_time')
                    ->time(),
                TextEntry::make('arrival_time')
                    ->time()
                    ->placeholder('-'),
                TextEntry::make('price')
                    ->money(),
                TextEntry::make('available_seats')
                    ->numeric(),
                TextEntry::make('status'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Schedule $record): bool => $record->trashed()),
            ]);
    }
}
