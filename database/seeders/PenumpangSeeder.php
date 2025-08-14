<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Penumpang;
use Carbon\Carbon;

class PenumpangSeeder extends Seeder
{
    public function run(): void
    {
        $sample = [
            ['nama_lengkap' => 'Siti Aminah', 'nik' => '3271010101010001', 'jenis_kelamin' => 'P', 'tanggal_lahir' => Carbon::parse('1990-01-01'), 'telepon' => '081355557777', 'email' => 'siti@example.com', 'alamat' => 'Jl. Mawar 1'],
            ['nama_lengkap' => 'Ahmad Fauzi', 'nik' => '3271010101010002', 'jenis_kelamin' => 'L', 'tanggal_lahir' => Carbon::parse('1988-05-05'), 'telepon' => '081355554444', 'email' => 'ahmad@example.com', 'alamat' => 'Jl. Melati 2'],
            ['nama_lengkap' => 'Dewi Sartika', 'nik' => '3271010101010003', 'jenis_kelamin' => 'P', 'tanggal_lahir' => Carbon::parse('1992-06-06'), 'telepon' => '081355553333', 'email' => 'dewi@example.com', 'alamat' => 'Jl. Kenanga 3'],
            ['nama_lengkap' => 'Fadli Akbar', 'nik' => '3271010101010004', 'jenis_kelamin' => 'L', 'tanggal_lahir' => Carbon::parse('1991-07-07'), 'telepon' => '081355552222', 'email' => 'fadli@example.com', 'alamat' => 'Jl. Cempaka 4'],
            ['nama_lengkap' => 'Nina Kurnia', 'nik' => null, 'jenis_kelamin' => 'P', 'tanggal_lahir' => Carbon::parse('1993-03-03'), 'telepon' => '081355551111', 'email' => null, 'alamat' => 'Jl. Flamboyan 5'],
            ['nama_lengkap' => 'Rian Pratama', 'nik' => null, 'jenis_kelamin' => 'L', 'tanggal_lahir' => Carbon::parse('1995-12-12'), 'telepon' => '081300000000', 'email' => null, 'alamat' => 'Jl. Kenanga 6'],
        ];

        foreach ($sample as $p) {
            Penumpang::create($p);
        }
    }
}
