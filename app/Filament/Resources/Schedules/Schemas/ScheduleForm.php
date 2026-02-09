<?php

namespace App\Filament\Resources\Schedules\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;

class ScheduleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('route_id')
                    ->required()
                    ->numeric(),
                TextInput::make('bus_id')
                    ->required()
                    ->numeric(),
                DatePicker::make('departure_date')
                    ->required(),
                TimePicker::make('departure_time')
                    ->required(),
                TimePicker::make('arrival_time'),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('available_seats')
                    ->required()
                    ->numeric(),
                TextInput::make('status')
                    ->required()
                    ->default('scheduled'),
            ]);
    }
}
