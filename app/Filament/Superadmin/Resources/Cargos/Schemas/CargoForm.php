<?php

namespace App\Filament\Superadmin\Resources\Cargos\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CargoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('booking_id')
                    ->required()
                    ->numeric(),
                TextInput::make('origin_agent_id')
                    ->required()
                    ->numeric(),
                TextInput::make('destination_agent_id')
                    ->required()
                    ->numeric(),
                TextInput::make('description')
                    ->required(),
                TextInput::make('weight_kg')
                    ->numeric(),
                TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('fee')
                    ->required()
                    ->numeric(),
                TextInput::make('recipient_name'),
                TextInput::make('recipient_phone')
                    ->tel(),
                Textarea::make('pickup_address')
                    ->columnSpanFull(),
                Textarea::make('dropoff_address')
                    ->columnSpanFull(),
                TextInput::make('dropoff_location_name'),
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
                TextInput::make('payment_type')
                    ->required()
                    ->default('paid_origin'),
                TextInput::make('payment_method'),
                Toggle::make('is_paid')
                    ->required(),
                DateTimePicker::make('paid_at'),
                TextInput::make('status')
                    ->required()
                    ->default('pending'),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
