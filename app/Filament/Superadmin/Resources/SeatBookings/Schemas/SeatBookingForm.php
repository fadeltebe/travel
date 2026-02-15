<?php

namespace App\Filament\Superadmin\Resources\SeatBookings\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SeatBookingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('schedule_id')
                    ->required()
                    ->numeric(),
                TextInput::make('passenger_id')
                    ->required()
                    ->numeric(),
                TextInput::make('bus_layout_seat_id')
                    ->required()
                    ->numeric(),
                TextInput::make('seat_number')
                    ->required(),
                TextInput::make('status')
                    ->required()
                    ->default('booked'),
            ]);
    }
}
