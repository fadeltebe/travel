<?php

namespace App\Filament\Admin\Resources\Jadwals\Schemas;

use Filament\Support\RawJs;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;

class JadwalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('rute_id')
                    ->label('Rute')
                    ->options(function () {
                        // Ambil nama kota agen/admin yang sedang login
                        $user = auth()->user();
                        $kota = $user->agens()->first()?->kota ?? null; // pastikan relasi agens ada di model User

                        if (!$kota) {
                            return [];
                        }

                        // Hanya rute dari kota agen ke kota lain (bukan ke kota sendiri)
                        return \App\Models\Rute::where('kota_asal', $kota)
                            ->where('kota_tujuan', '!=', $kota)
                            ->get()
                            ->mapWithKeys(function ($rute) {
                                return [
                                    $rute->id => "{$rute->kota_asal} - {$rute->kota_tujuan}"
                                ];
                            });
                    })
                    ->searchable()
                    ->required()
                    ->afterStateUpdated(function ($state, callable $set) {
                        $hargaDasar = \App\Models\Rute::find($state)?->harga_dasar;
                        $set('harga', $hargaDasar);
                    }),
                TextInput::make('harga')
                    ->required()
                    ->readOnly()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        if ($get('rute_id')) {
                            $hargaDasar = \App\Models\Rute::find($get('rute_id'))?->harga_dasar;
                            $set('harga', $hargaDasar);
                        }
                    })
                    ->mask(RawJs::make('$money($input)'))
                    ->stripCharacters(',')
                    ->numeric(),
                Select::make('mobil_id')
                    ->relationship('mobil', 'nomor_polisi')
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('kode_jadwal')
                    ->required(),
                DatePicker::make('tanggal')
                    ->required(),
                TimePicker::make('jam_berangkat')
                    ->required(),
                TimePicker::make('jam_tiba_estimasi')
                    ->required(),
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
