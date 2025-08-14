<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Agen;

class AgenSeeder extends Seeder
{
    public function run(): void
    {
        Agen::insert([
            [
                'name' => 'Agen Palu',
                'kode_agen' => 'AGP01',
                'kota' => 'Palu',
                'alamat' => 'Jl. Sudirman No. 1',
                'telepon' => '0811111111',
                'email' => 'palu@travelnusantara.com',
                'is_active' => true
            ],
            [
                'name' => 'Agen Ampana',
                'kode_agen' => 'AGA02',
                'kota' => 'Ampana',
                'alamat' => 'Jl. Ahmad Yani No. 5',
                'telepon' => '0822222222',
                'email' => 'ampana@travelnusantara.com',
                'is_active' => true
            ],
            [
                'name' => 'Agen Luwuk',
                'kode_agen' => 'AGL03',
                'kota' => 'Luwuk',
                'alamat' => 'Jl. Sam Ratulangi No. 7',
                'telepon' => '0833333333',
                'email' => 'luwuk@travelnusantara.com',
                'is_active' => true
            ]
        ]);
    }
}
