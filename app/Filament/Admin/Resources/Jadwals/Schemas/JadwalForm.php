<?php

namespace App\Filament\Admin\Resources\Jadwals\Schemas;

use Filament\Forms;
use App\Models\Rute;
use App\Models\Mobil;
use Filament\Support\RawJs;
use Illuminate\Support\Str;
use Filament\Schemas\Schema;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;

class JadwalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Jadwal')->schema([
                    Forms\Components\TextInput::make('kode_jadwal')
                        ->label('Kode Jadwal')
                        ->required()
                        ->default(fn() => 'JDL-' . strtoupper(Str::random(10)))
                        ->unique(ignoreRecord: true),

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
                        ->preload()
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set) {
                            $hargaDasar = \App\Models\Rute::find($state)?->harga_dasar ?? null;
                            $set('harga', $hargaDasar);
                        }),

                    Forms\Components\TextInput::make('harga')
                        ->label('Harga')
                        ->required()
                        ->mask(RawJs::make('$money($input)'))
                        ->stripCharacters('.')
                        ->minValue(0)
                        ->prefix('Rp')
                        ->afterStateUpdated(function (callable $set, $state) {
                            // Format harga dengan pemisah ribuan titik
                            $formatted = number_format((float) str_replace('.', '', $state), 0, ',', '.');
                            $set('harga', $formatted);
                        })
                        ->dehydrateStateUsing(fn($state) => (float) str_replace('.', '', $state))
                        ->default(0),


                    Forms\Components\Select::make('mobil_id')
                        ->label('Mobil')
                        ->relationship('mobil', 'nomor_polisi')
                        ->getOptionLabelFromRecordUsing(function ($record) {
                            return $record->nomor_polisi . ' (' . $record->merk . ' ' . $record->tipe . ')';
                        })
                        ->required()
                        ->preload()
                        ->searchable()
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set) {
                            if ($state) {
                                $kapasitas = \App\Models\Mobil::find($state)?->kapasitas ?? null;
                                $set('kursi_tersedia', $kapasitas);
                            } else {
                                $set('kursi_tersedia', null);
                            }
                        }),

                    Forms\Components\TextInput::make('kursi_tersedia')
                        ->label('Kursi Tersedia')
                        ->numeric()
                        ->required(),

                    // Forms\Components\Select::make('driver_id')
                    //     ->label('Driver')
                    //     ->relationship('driver', 'nama')
                    //     ->required()
                    //     ->searchable(),

                    // Forms\Components\Select::make('crew_id')
                    //     ->label('Crew')
                    //     ->relationship('crew', 'nama')
                    //     ->searchable(),

                    Forms\Components\Select::make('drivers')
                        ->label('Drivers')
                        ->relationship('drivers', 'nama')
                        ->multiple()
                        ->preload()
                        ->required()
                        ->searchable(),

                    Forms\Components\Select::make('crews')
                        ->label('Crews')
                        ->relationship('crews', 'nama')
                        ->preload()
                        ->multiple()
                        ->searchable(),

                    Forms\Components\Select::make('penumpangs')
                        ->label('Penumpang')
                        ->relationship('penumpangs', 'nama') // pastikan relasi exists di model Jadwal
                        ->multiple()
                        ->preload()
                        ->searchable(),
                ]),

                Section::make('Waktu')->schema([
                    Forms\Components\TimePicker::make('jam_berangkat')
                        ->required(),
                    Forms\Components\TimePicker::make('jam_tiba_estimasi')
                        ->required(),

                    Forms\Components\DatePicker::make('tanggal')
                        ->required()
                        ->default(now()),

                    Select::make('status')
                        ->options([
                            'Dijadwalkan' => 'Dijadwalkan',
                            'Berangkat' => 'Berangkat',
                            'Tiba' => 'Tiba',
                            'Dibatalkan' => 'Dibatalkan',
                        ])
                        ->default('Dijadwalkan')
                        ->required(),

                    Forms\Components\Textarea::make('keterangan')
                        ->rows(3),
                ]),

                Section::make('Daftar Pemesan & Penumpang')
                    ->schema([
                        Repeater::make('pemesanan')
                            ->relationship('pemesanans') // relasi ini HARUS ada di model Jadwal
                            ->schema([
                                Select::make('pemesan_id')
                                    ->label('Pemesan')
                                    ->relationship('pemesan', 'nama') // relasi di model Pemesanan
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Repeater::make('pemesananPenumpangs')
                                    ->relationship('pemesananPenumpangs') // relasi di model Pemesanan
                                    ->schema([
                                        Select::make('penumpang_id')
                                            ->label('Penumpang')
                                            ->relationship('penumpang', 'nama') // relasi di model PemesananPenumpang
                                            ->preload()
                                            ->searchable()
                                            ->required(),
                                    ]),
                            ]),
                    ]),

            ]);
    }
}
