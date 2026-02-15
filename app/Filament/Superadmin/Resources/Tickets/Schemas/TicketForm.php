<?php

namespace App\Filament\Superadmin\Resources\Tickets\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class TicketForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('booking_id')
                    ->required()
                    ->numeric(),
                TextInput::make('passenger_id')
                    ->required()
                    ->numeric(),
                TextInput::make('ticket_number')
                    ->required(),
                Textarea::make('qr_code')
                    ->columnSpanFull(),
                TextInput::make('status')
                    ->required()
                    ->default('active'),
                DateTimePicker::make('scanned_at'),
                TextInput::make('scanned_by')
                    ->numeric(),
            ]);
    }
}
