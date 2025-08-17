<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str; // Digunakan untuk slug
use App\Models\Rute;
use App\Models\Agen; // Model Agen tidak diperlukan, jadi bisa dihapus

class RuteSeeder extends Seeder
{
    /**
     * Jalankan seeder database.
     */
    public function run(): void
    {
        // Data sampel untuk rute
        $sample = [
            ['kota_asal' => 'Palu', 'kota_tujuan' => 'Ampana', 'jarak_km' => 150, 'estimasi_waktu' => 180, 'harga_dasar' => 250000],
            ['kota_asal' => 'Palu', 'kota_tujuan' => 'Luwuk', 'jarak_km' => 220, 'estimasi_waktu' => 240, 'harga_dasar' => 300000],
            ['kota_asal' => 'Ampana', 'kota_tujuan' => 'Luwuk', 'jarak_km' => 80, 'estimasi_waktu' => 90, 'harga_dasar' => 120000],
            ['kota_asal' => 'Ampana', 'kota_tujuan' => 'Palu', 'jarak_km' => 150, 'estimasi_waktu' => 180, 'harga_dasar' => 250000],
            ['kota_asal' => 'Luwuk', 'kota_tujuan' => 'Palu', 'jarak_km' => 220, 'estimasi_waktu' => 240, 'harga_dasar' => 300000],
        ];

        // Looping untuk membuat setiap rute dari data sampel
        foreach ($sample as $data) {
            // Membuat kode rute yang unik dan mudah dibaca
            // Contoh: PLU-AMP atau AMP-LWK
            $kode_rute = Str::upper(Str::substr($data['kota_asal'], 0, 3)) . '-' . Str::upper(Str::substr($data['kota_tujuan'], 0, 3));

            // Buat atau perbarui rute. Jika sudah ada, lewati.
            Rute::firstOrCreate(
                ['kode_rute' => $kode_rute],
                [
                    'kota_asal' => $data['kota_asal'],
                    'kota_tujuan' => $data['kota_tujuan'],
                    'jarak_km' => $data['jarak_km'],
                    'estimasi_waktu' => $data['estimasi_waktu'],
                    'harga_dasar' => $data['harga_dasar'],
                    'is_active' => true,
                ]
            );
        }
    }
}
