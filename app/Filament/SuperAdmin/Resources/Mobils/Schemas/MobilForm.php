<?php

namespace App\Filament\Admin\Resources\Mobils\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class MobilForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nomor_polisi')
                    ->required(),
                TextInput::make('nomor_mesin')
                    ->required(),
                TextInput::make('nomor_rangka')
                    ->required(),
                TextInput::make('tahun_perakitan')
                    ->required(),
                TextInput::make('merk')
                    ->required(),
                TextInput::make('model')
                    ->required(),
                TextInput::make('tahun')
                    ->required()
                    ->numeric(),
                TextInput::make('kapasitas')
                    ->required()
                    ->numeric(),
                Select::make('tipe')
                    ->options(['Bus' => 'Bus', 'Mini Bus' => 'Mini bus', 'SUV' => 'S u v', 'MPV' => 'M p v'])
                    ->required(),
                Select::make('kelas')
                    ->options([
            'Ekonomi' => 'Ekonomi',
            'Bisnis' => 'Bisnis',
            'Sleeper' => 'Sleeper',
            'Executive' => 'Executive',
        ])
                    ->required(),
                Textarea::make('fasilitas')
                    ->columnSpanFull(),
                Select::make('status')
                    ->options(['Aktif' => 'Aktif', 'Maintenance' => 'Maintenance', 'Nonaktif' => 'Nonaktif'])
                    ->default('Aktif')
                    ->required(),
            ]);
    }
}
