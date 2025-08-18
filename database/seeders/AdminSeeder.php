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
            $email = strtolower(str_replace(' ', '', $agen->name)) . '@example.com';

            // Cek jika user sudah ada berdasarkan email
            $user = User::where('email', $email)->first();

            // Jika belum ada, buat user baru
            if (!$user) {
                $user = User::create([
                    'name'     => 'Admin ' . $agen->name,
                    'email'    => $email,
                    'password' => Hash::make('password123'),
                ]);
            }

            // Buat admin yang terkait ke agen
            Admin::create([
                'user_id'   => $user->id,
                'agen_id'   => $agen->id,
                'nama'      => 'Admin ' . $agen->name,
                'nik'       => fake()->numerify('7201##########'),
                'alamat'    => fake()->address(),
                'telepon'   => fake()->phoneNumber(),
                'foto'      => null,
                'status'    => 'aktif',
            ]);
        }
    }
}
