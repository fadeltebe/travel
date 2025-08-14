<?php

namespace App\Filament\Resources\Agens\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AgenForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama')
                    ->required(),
                TextInput::make('kode_agen')
                    ->required(),
                TextInput::make('kota')
                    ->required(),
                TextInput::make('alamat')
                    ->required(),
                TextInput::make('telepon')
                    ->tel()
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
