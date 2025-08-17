<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Mobil;

class MobilSeeder extends Seeder
{
    public function run(): void
    {
        Mobil::insert([
            [
                'nomor_polisi' => 'DN 1234 AB',
                'nomor_mesin' => 'ENG123456',
                'nomor_rangka' => 'FRM123456',
                'tahun_perakitan' => '2020',
                'tahun_perolehan' => 2020,
                'merk' => 'Toyota',
                'model' => 'Hiace',
                'kapasitas' => 15,
                'tipe' => 'Mini Bus',
                'kelas' => 'Bisnis',
                'fasilitas' => 'AC, Reclining Seat',
                'status' => 'Aktif',
                'warna' => 'Putih'
            ],
            [
                'nomor_polisi' => 'DN 5678 CD',
                'nomor_mesin' => 'ENG789012',
                'nomor_rangka' => 'FRM789012',
                'tahun_perakitan' => '2019',
                'merk' => 'Mitsubishi',
                'model' => 'L300',
                'tahun' => 2019,
                'kapasitas' => 12,
                'tipe' => 'Mini Bus',
                'kelas' => 'Ekonomi',
                'fasilitas' => 'AC',
                'status' => 'Aktif',
                'warna' => 'Hitam'
            ]
        ]);
    }
}
