<?php

namespace App\Filament\Superadmin\Resources\CargoReceipts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class CargoReceiptForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('cargo_id')
                    ->required()
                    ->numeric(),
                TextInput::make('receipt_number')
                    ->required(),
                Textarea::make('qr_code')
                    ->columnSpanFull(),
                TextInput::make('received_by_name')
                    ->required(),
                TextInput::make('received_by_phone')
                    ->tel(),
                DateTimePicker::make('received_at')
                    ->required(),
                TextInput::make('agent_id')
                    ->required()
                    ->numeric(),
                TextInput::make('handler_user_id')
                    ->numeric(),
                Textarea::make('signature_photo')
                    ->columnSpanFull(),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
