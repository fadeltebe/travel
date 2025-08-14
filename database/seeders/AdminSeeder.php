<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use App\Models\User;
use App\Models\Agen;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil semua agen
        $agens = Agen::all();

        foreach ($agens as $agen) {
            // Buat user baru untuk setiap agen
            $user = User::create([
                'name'     => 'Admin ' . $agen->name,
                'email'    => strtolower(str_replace(' ', '', $agen->name)) . '@example.com',
                'password' => Hash::make('password123'),
            ]);

            // Buat admin yang terkait ke agen
            Admin::create([
                'user_id'   => $user->id,
                'agen_id'   => $agen->id,
                'nama'      => 'Admin ' . $agen->name,
                'nik'       => fake()->numerify('7201##########'),
                'alamat'    => fake()->address(),
                'nomor_hp'  => fake()->phoneNumber(),
                'foto'      => null,
                'status'    => 'aktif',
            ]);
        }
    }
}
