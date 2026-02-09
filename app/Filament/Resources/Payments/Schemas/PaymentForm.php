<?php

namespace App\Filament\Resources\Payments\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('booking_id')
                    ->required()
                    ->numeric(),
                TextInput::make('payment_type')
                    ->required()
                    ->default('booking'),
                TextInput::make('amount')
                    ->required()
                    ->numeric(),
                TextInput::make('method')
                    ->required(),
                TextInput::make('reference_number'),
                TextInput::make('paid_by'),
                TextInput::make('received_by')
                    ->numeric(),
                TextInput::make('agent_id')
                    ->required()
                    ->numeric(),
                DateTimePicker::make('paid_at')
                    ->required(),
                Textarea::make('proof_photo')
                    ->columnSpanFull(),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
