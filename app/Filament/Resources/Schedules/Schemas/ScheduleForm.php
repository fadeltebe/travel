<?php

namespace App\Filament\Resources\Schedules\Schemas;


use Filament\Schemas\Schema;
use Filament\Facades\Filament;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Utilities\Set;

class ScheduleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('route_id')
                    ->label('Route')
                    ->relationship(
                        name: 'route',
                        titleAttribute: 'origin_city',
                        modifyQueryUsing: function (Builder $query) {
                            $tenant = Filament::getTenant(); // Current agent

                            // ✨ Filter: hanya rute yang origin_city = agent city
                            return $query->where('origin_city', $tenant->city);
                        }
                    )
                    ->getOptionLabelFromRecordUsing(fn($record) => "{$record->origin_city} → {$record->destination_city}")
                    ->searchable()
                    ->preload()
                    ->required()
                    ->afterStateUpdated(function (Set $set, $state) {
                        if ($state) {
                            $route = \App\Models\Route::find($state);
                            $set('price', $route?->base_price); // ✨ Set price otomatis
                        }
                    }),
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
                    ->default(now())
                    ->required(),
                TimePicker::make('departure_time')
                    ->required(),
                DatePicker::make('arrival_date')
                    ->default(now())
                    ->required(),
                TimePicker::make('arrival_time')
                    ->required(),
                TextInput::make('price')
                    ->label('Price')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),
                TextInput::make('available_seats')
                    ->required()
                    ->numeric(),
                ToggleButtons::make('status')
                    ->label('Status')
                    ->options([
                        'scheduled' => 'Dijadwalkan',
                        'departed' => 'Dalam Perjalanan',
                        'arrived' => 'Tiba',
                        'cancelled' => 'Dibatalkan',
                    ])
                    ->colors([
                        'scheduled' => 'primary',
                        'departed' => 'warning',
                        'arrived' => 'success',
                        'cancelled' => 'danger',
                    ])
                    ->icons([
                        'scheduled' => 'heroicon-o-calendar',
                        'departed' => 'heroicon-o-truck',
                        'arrived' => 'heroicon-o-check',
                        'cancelled' => 'heroicon-o-x-mark',
                    ])
                    ->inline()
                    ->grouped()
                    ->default('scheduled')
                    ->required(),
            ]);
    }
}
