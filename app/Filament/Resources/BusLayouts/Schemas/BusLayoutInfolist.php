<?php

namespace App\Filament\Resources\BusLayouts\Schemas;

use App\Models\BusLayout;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class BusLayoutInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('type')
                    ->placeholder('-'),
                TextEntry::make('total_rows')
                    ->numeric(),
                TextEntry::make('total_columns')
                    ->numeric(),
                TextEntry::make('total_seats')
                    ->numeric(),
                TextEntry::make('description')
                    ->placeholder('-')
                    ->columnSpanFull(),
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
                    ->visible(fn (BusLayout $record): bool => $record->trashed()),
            ]);
    }
}
