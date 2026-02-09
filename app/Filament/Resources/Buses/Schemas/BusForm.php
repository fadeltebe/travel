<?php

namespace App\Filament\Resources\Buses\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BusForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('plate_number')
                    ->required(),
                TextInput::make('brand'),
                TextInput::make('machine_number'),
                TextInput::make('chassis_number'),
                TextInput::make('name'),
                TextInput::make('type'),
                TextInput::make('bus_layout_id')
                    ->numeric(),
                TextInput::make('total_seats')
                    ->required()
                    ->numeric(),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
