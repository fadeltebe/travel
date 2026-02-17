<?php

namespace App\Filament\Superadmin\Resources\Routes\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class RouteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('origin_agent_id')
                    ->label('Asal')
                    ->options(function () {
                        return \App\Models\Agent::where('is_active', true)->pluck('name', 'id');
                    })
                    ->searchable()
                    ->required(),
                Select::make('destination_agent_id')
                    ->label('Tujuan')
                    ->options(function () {
                        return \App\Models\Agent::where('is_active', true)->pluck('name', 'id');
                    })
                    ->searchable()
                    ->required(),
                TextInput::make('distance_km')
                    ->numeric(),
                TextInput::make('estimated_duration_minutes')
                    ->numeric(),
                TextInput::make('base_price')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
