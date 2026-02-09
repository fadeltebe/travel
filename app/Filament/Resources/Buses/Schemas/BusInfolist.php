<?php

namespace App\Filament\Resources\Buses\Schemas;

use App\Models\Bus;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class BusInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('plate_number'),
                TextEntry::make('brand')
                    ->placeholder('-'),
                TextEntry::make('machine_number')
                    ->placeholder('-'),
                TextEntry::make('chassis_number')
                    ->placeholder('-'),
                TextEntry::make('name')
                    ->placeholder('-'),
                TextEntry::make('type')
                    ->placeholder('-'),
                TextEntry::make('bus_layout_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('total_seats')
                    ->numeric(),
                IconEntry::make('is_active')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Bus $record): bool => $record->trashed()),
            ]);
    }
}
