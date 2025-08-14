<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            TravelSeeder::class,
            AgenSeeder::class,
            AdminSeeder::class,
            AgenUserSeeder::class,
            MobilSeeder::class,
            DriverSeeder::class,
            CrewSeeder::class,
            RuteSeeder::class,
            JadwalSeeder::class,
            PenumpangSeeder::class,
            PemesananSeeder::class,
            TiketSeeder::class,
            PembayaranSeeder::class,
        ]);
    }
}
