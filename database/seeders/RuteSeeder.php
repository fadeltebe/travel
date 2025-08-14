<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rute;
use App\Models\Agen;

class RuteSeeder extends Seeder
{
    public function run(): void
    {
        $agens = Agen::all();

        $sample = [
            ['kota_asal' => 'Palu', 'kota_tujuan' => 'Ampana', 'jarak_km' => 150, 'estimasi_waktu' => 180, 'harga_dasar' => 250000],
            ['kota_asal' => 'Palu', 'kota_tujuan' => 'Luwuk', 'jarak_km' => 220, 'estimasi_waktu' => 240, 'harga_dasar' => 300000],
            ['kota_asal' => 'Ampana', 'kota_tujuan' => 'Luwuk', 'jarak_km' => 80, 'estimasi_waktu' => 90, 'harga_dasar' => 120000],
            ['kota_asal' => 'Ampana', 'kota_tujuan' => 'Palu', 'jarak_km' => 150, 'estimasi_waktu' => 180, 'harga_dasar' => 250000],
            ['kota_asal' => 'Luwuk', 'kota_tujuan' => 'Palu', 'jarak_km' => 220, 'estimasi_waktu' => 240, 'harga_dasar' => 300000],
        ];

        $i = 1;
        foreach ($sample as $s) {
            // assign agen in round-robin
            $agen = $agens->skip(($i - 1) % $agens->count())->first();

            Rute::create([
                'agen_id' => $agen->id,
                'kode_rute' => 'RTE-' . strtoupper(uniqid()),
                'kota_asal' => $s['kota_asal'],
                'kota_tujuan' => $s['kota_tujuan'],
                'jarak_km' => $s['jarak_km'],
                'estimasi_waktu' => $s['estimasi_waktu'],
                'harga_dasar' => $s['harga_dasar'],
                'is_active' => true,
            ]);

            $i++;
        }
    }
}
