<?php

namespace App\Filament\SuperAdmin\Resources\Jadwals\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;

class JadwalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('agen_id')
                    ->relationship('agen', 'name')
                    ->required(),
                Select::make('rute_id')
                    ->relationship('rute', 'id')
                    ->required(),
                Select::make('mobil_id')
                    ->relationship('mobil', 'id')
                    ->required(),
                TextInput::make('kode_jadwal')
                    ->required(),
                DatePicker::make('tanggal')
                    ->required(),
                TimePicker::make('jam_berangkat')
                    ->required(),
                TimePicker::make('jam_tiba_estimasi')
                    ->required(),
                TextInput::make('harga')
                    ->required()
                    ->numeric(),
                TextInput::make('kursi_tersedia')
                    ->required()
                    ->numeric(),
                Select::make('status')
                    ->options([
                        'Dijadwalkan' => 'Dijadwalkan',
                        'Berangkat' => 'Berangkat',
                        'Tiba' => 'Tiba',
                        'Dibatalkan' => 'Dibatalkan',
                    ])
                    ->default('Dijadwalkan')
                    ->required(),
                Textarea::make('catatan')
                    ->columnSpanFull(),
            ]);
    }
}
