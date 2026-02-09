<?php

namespace App\Filament\Resources\BusLayoutSeats\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BusLayoutSeatForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('bus_layout_id')
                    ->required()
                    ->numeric(),
                TextInput::make('row')
                    ->required()
                    ->numeric(),
                TextInput::make('column')
                    ->required()
                    ->numeric(),
                TextInput::make('seat_number'),
                TextInput::make('type')
                    ->required(),
                TextInput::make('label'),
                TextInput::make('capacity')
                    ->required()
                    ->numeric()
                    ->default(1),
                Toggle::make('is_available')
                    ->required(),
            ]);
    }
}
