<?php

namespace App\Filament\Superadmin\Resources\Schedules\Schemas;

use App\Models\Route;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;

class ScheduleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('route_id')
                    ->label('Rute')
                    ->relationship(
                        name: 'route',
                        modifyQueryUsing: fn($query) =>
                        $query->with([
                            'originAgent:id,city',
                            'destinationAgent:id,city',
                        ])
                    )
                    ->getOptionLabelFromRecordUsing(
                        fn(Route $record) =>
                        $record->originAgent->city
                            . ' → '
                            . $record->destinationAgent->city
                    )
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('bus_id')
                    ->label('Bus')
                    ->relationship(
                        name: 'bus',
                        titleAttribute: 'plate_number',
                    )
                    ->searchable()
                    ->preload()
                    ->required(),
                DatePicker::make('departure_date')
                    ->required(),
                TimePicker::make('departure_time')
                    ->required(),
                DatePicker::make('arrival_date'),
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
