<?php

namespace App\Filament\Resources\Routes\Schemas;

use App\Models\Route;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class RouteInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('origin_city'),
                TextEntry::make('destination_city'),
                TextEntry::make('distance_km')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('estimated_duration_minutes')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('base_price')
                    ->money(),
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
                    ->visible(fn (Route $record): bool => $record->trashed()),
            ]);
    }
}
