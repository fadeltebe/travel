<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CompanySeeder::class,
            AgentSeeder::class,
            UserSeeder::class,
            BusLayoutSeeder::class,
            BusSeeder::class,
            RouteSeeder::class,
            AgentScheduleSeeder::class, // Seeder utama untuk Agen, Rute, Jadwal, Booking, Penumpang, dan Kargo
            ScheduleSeatSeeder::class, // Seeder untuk mengisi kursi pada jadwal
            // Bookings, Passengers, and Cargos are handled inside ScheduleSeeder orchestrator
        ]);
    }
}
