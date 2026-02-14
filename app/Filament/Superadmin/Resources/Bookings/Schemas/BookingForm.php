<?php

namespace App\Filament\Superadmin\Resources\Bookings\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class BookingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('booking_code')
                    ->required(),
                TextInput::make('schedule_id')
                    ->required()
                    ->numeric(),
                TextInput::make('agent_id')
                    ->required()
                    ->numeric(),
                TextInput::make('customer_id')
                    ->numeric(),
                TextInput::make('booker_name')
                    ->required(),
                TextInput::make('booker_phone')
                    ->tel()
                    ->required(),
                TextInput::make('booker_email')
                    ->email(),
                TextInput::make('total_passengers')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_cargo')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('subtotal_price')
                    ->required()
                    ->numeric()
                    ->default(0.0)
                    ->prefix('$'),
                TextInput::make('cargo_fee')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('cargo_cod_fee')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('pickup_dropoff_fee')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('total_price')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('payment_status')
                    ->required()
                    ->default('pending'),
                TextInput::make('payment_method'),
                DateTimePicker::make('paid_at'),
                Textarea::make('notes')
                    ->columnSpanFull(),
                TextInput::make('status')
                    ->required()
                    ->default('confirmed'),
            ]);
    }
}
