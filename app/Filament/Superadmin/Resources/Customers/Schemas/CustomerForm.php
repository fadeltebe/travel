<?php

namespace App\Filament\Superadmin\Resources\Customers\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('phone')
                    ->tel()
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email(),
                TextInput::make('id_card_number'),
                Textarea::make('address')
                    ->columnSpanFull(),
                TextInput::make('password')
                    ->password(),
                DateTimePicker::make('email_verified_at'),
                Toggle::make('is_active')
                    ->required(),
                TextInput::make('total_bookings')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_trips')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_shipments')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
