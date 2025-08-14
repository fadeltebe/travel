<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Travel;

class TravelSeeder extends Seeder
{
    public function run(): void
    {
        Travel::create([
            'nama' => 'Travel Nusantara',
            'alamat' => 'Jl. Merdeka No. 1, Palu',
            'telepon' => '081234567890',
            'email' => 'info@travelnusantara.com',
            'deskripsi' => 'Penyedia jasa travel antar kota',
            'is_active' => true
        ]);
    }
}
