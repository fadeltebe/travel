<?php

namespace App\Filament\Superadmin\Resources\Passengers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PassengerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('booking_id')
                    ->required()
                    ->numeric(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('id_card_number'),
                TextInput::make('phone')
                    ->tel(),
                TextInput::make('seat_number'),
                Toggle::make('is_booker')
                    ->required(),
                Textarea::make('pickup_address')
                    ->columnSpanFull(),
                Textarea::make('dropoff_address')
                    ->columnSpanFull(),
                TextInput::make('pickup_fee')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('dropoff_fee')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                Toggle::make('need_pickup')
                    ->required(),
                Toggle::make('need_dropoff')
                    ->required(),
            ]);
    }
}
