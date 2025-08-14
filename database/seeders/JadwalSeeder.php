<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Jadwal;
use App\Models\Rute;
use App\Models\Mobil;
use App\Models\Driver;
use App\Models\Crew;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class JadwalSeeder extends Seeder
{
    public function run(): void
    {
        $mobils = Mobil::all();
        $drivers = Driver::all();
        $crews = Crew::all();

        foreach (Rute::all() as $rute) {
            // buat 3 jadwal per rute pada beberapa tanggal
            for ($i = 1; $i <= 3; $i++) {
                $mobil = $mobils->random();
                $jadwalDate = Carbon::now()->addDays($i);
                $jamBerangkat = Carbon::parse('08:00:00');
                $jamTiba = $jamBerangkat->copy()->addMinutes($rute->estimasi_waktu);

                $jadwal = Jadwal::create([
                    'agen_id' => $rute->agen_id,
                    'rute_id' => $rute->id,
                    'mobil_id' => $mobil->id,
                    'kode_jadwal' => 'JDL-' . strtoupper(uniqid()),
                    'tanggal' => $jadwalDate->toDateString(),
                    'jam_berangkat' => $jamBerangkat->format('H:i:s'),
                    'jam_tiba_estimasi' => $jamTiba->format('H:i:s'),
                    'harga' => $rute->harga_dasar,
                    'kursi_tersedia' => $mobil->kapasitas,
                    'status' => 'Dijadwalkan',
                    'catatan' => null,
                ]);

                // attach 1-2 driver secara pivot
                $assignedDrivers = $drivers->random(rand(1, 2));
                foreach ($assignedDrivers as $d) {
                    DB::table('jadwal_driver')->insert([
                        'jadwal_id' => $jadwal->id,
                        'driver_id' => $d->id,
                    ]);
                }

                // attach 1-2 crew secara pivot
                $assignedCrews = $crews->random(rand(1, 2));
                foreach ($assignedCrews as $c) {
                    DB::table('jadwal_crew')->insert([
                        'jadwal_id' => $jadwal->id,
                        'crew_id' => $c->id,
                    ]);
                }
            }
        }
    }
}
