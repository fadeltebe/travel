<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PemesanSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('pemesans')->insert([
            [
                'nama' => 'Budi Santoso',
                'telepon' => '081234567890',
                'email' => 'budi@example.com',
                'alamat' => 'Jl. Merdeka No. 10',
                'nik' => '3271010101010010',
                'tanggal_lahir' => Carbon::parse('1985-04-12'),
                'jenis_kelamin' => 'L',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Siti Rahmawati',
                'telepon' => '082345678901',
                'email' => 'siti@example.com',
                'alamat' => 'Jl. Sudirman No. 20',
                'nik' => '3271010101010020',
                'tanggal_lahir' => Carbon::parse('1990-08-25'),
                'jenis_kelamin' => 'P',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama' => 'Andi Wijaya',
                'telepon' => '083456789012',
                'email' => 'andi@example.com',
                'alamat' => 'Jl. Ahmad Yani No. 5',
                'nik' => '3271010101010030',
                'tanggal_lahir' => Carbon::parse('1988-12-05'),
                'jenis_kelamin' => 'L',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
