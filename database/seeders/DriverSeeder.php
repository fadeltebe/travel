<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Driver;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class DriverSeeder extends Seeder
{
    public function run(): void
    {
        $drivers = [
            [
                'nama' => 'Budi Santoso',
                'telepon' => '081234567890',
                'alamat' => 'Jl. Matoa No.1, Palu',
                'nik' => '7201010101010001',
                'tempat_lahir' => 'Palu',
                'tanggal_lahir' => Carbon::parse('1985-03-12'),
                'jenis_kelamin' => 'L',
                'jenis_sim' => 'B1',
            ],
            [
                'nama' => 'Andi Saputra',
                'telepon' => '081298765432',
                'alamat' => 'Jl. Merdeka No.2, Ampana',
                'nik' => '7201020202020002',
                'tempat_lahir' => 'Ampana',
                'tanggal_lahir' => Carbon::parse('1990-07-05'),
                'jenis_kelamin' => 'L',
                'jenis_sim' => 'B1',
            ],
            [
                'nama' => 'Rizal Fahmi',
                'telepon' => '081355551234',
                'alamat' => 'Jl. Kenanga No.3, Luwuk',
                'nik' => '7201030303030003',
                'tempat_lahir' => 'Luwuk',
                'tanggal_lahir' => Carbon::parse('1988-11-20'),
                'jenis_kelamin' => 'L',
                'jenis_sim' => 'B2',
            ],
            [
                'nama' => 'Hendra Wijaya',
                'telepon' => '081355559876',
                'alamat' => 'Jl. Dahlia No.4',
                'nik' => '7201040404040004',
                'tempat_lahir' => 'Palu',
                'tanggal_lahir' => Carbon::parse('1982-01-10'),
                'jenis_kelamin' => 'L',
                'jenis_sim' => 'B1',
            ],
            [
                'nama' => 'Sulastri',
                'telepon' => '081300011122',
                'alamat' => 'Jl. Melati No.5',
                'nik' => '7201050505050005',
                'tempat_lahir' => 'Ampana',
                'tanggal_lahir' => Carbon::parse('1992-05-22'),
                'jenis_kelamin' => 'P',
                'jenis_sim' => 'B1',
            ],
            [
                'nama' => 'Agus Salim',
                'telepon' => '081300022233',
                'alamat' => 'Jl. Flamboyan No.6',
                'nik' => '7201060606060006',
                'tempat_lahir' => 'Luwuk',
                'tanggal_lahir' => Carbon::parse('1987-09-09'),
                'jenis_kelamin' => 'L',
                'jenis_sim' => 'B2',
            ],
        ];

        foreach ($drivers as $d) {
            $email = strtolower(str_replace(' ', '', $d['nama'])) . '@driver.com';

            // Cek jika user sudah ada berdasarkan email
            $user = User::where('email', $email)->first();

            // Jika belum ada, buat user baru
            if (!$user) {
                $user = User::create([
                    'name'     => $d['nama'],
                    'email'    => $email,
                    'password' => Hash::make('password123'),
                ]);
            }

            $d['user_id'] = $user->id;

            Driver::create($d);
        }
    }
}
