<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Jadwal;
use App\Models\Driver;
use Illuminate\Support\Facades\DB;

class JadwalDriverSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $drivers = Driver::all();

        foreach (Jadwal::all() as $jadwal) {
            // Pilih 1-2 driver secara acak untuk setiap jadwal
            $assignedDrivers = $drivers->random(rand(1, min(2, $drivers->count())));

            foreach ($assignedDrivers as $driver) {
                DB::table('jadwal_driver')->updateOrInsert(
                    [
                        'jadwal_id' => $jadwal->id,
                        'driver_id' => $driver->id,
                    ],
                    []
                );
            }
        }
    }
}
