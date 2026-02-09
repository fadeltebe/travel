<?php

namespace App\Filament\Resources\BusLayoutSeats\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class BusLayoutSeatInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('bus_layout_id')
                    ->numeric(),
                TextEntry::make('row')
                    ->numeric(),
                TextEntry::make('column')
                    ->numeric(),
                TextEntry::make('seat_number')
                    ->placeholder('-'),
                TextEntry::make('type'),
                TextEntry::make('label')
                    ->placeholder('-'),
                TextEntry::make('capacity')
                    ->numeric(),
                IconEntry::make('is_available')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
