<?php

namespace App\Filament\Admin\Resources\Rutes\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class RuteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('agen_id')
                    ->relationship('agen', 'name')
                    ->required(),
                TextInput::make('kode_rute')
                    ->required(),
                TextInput::make('kota_asal')
                    ->required(),
                TextInput::make('kota_tujuan')
                    ->required(),
                TextInput::make('jarak_km')
                    ->required()
                    ->numeric(),
                TextInput::make('estimasi_waktu')
                    ->required()
                    ->numeric(),
                TextInput::make('harga_dasar')
                    ->required()
                    ->numeric(),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
