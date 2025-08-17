<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Jadwal;
use App\Models\Rute;
use App\Models\Mobil;
use App\Models\Driver;
use App\Models\Crew;
use App\Models\Agen; // Tambahkan model Agen
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class JadwalSeeder extends Seeder
{
    /**
     * Jalankan seeder database.
     */
    public function run(): void
    {
        // Pastikan tabel Rute, Mobil, Driver, Crew, dan Agen tidak kosong
        if (Mobil::count() === 0 || Driver::count() === 0 || Crew::count() === 0 || Agen::count() === 0) {
            echo "Pastikan seeder Mobil, Driver, Crew, dan Agen sudah dijalankan.\n";
            return;
        }

        $mobils = Mobil::all();
        $drivers = Driver::all();
        $crews = Crew::all();
        $agenIds = Agen::pluck('id')->toArray(); // Ambil semua ID agen

        foreach (Rute::all() as $rute) {
            // buat 3 jadwal per rute pada beberapa tanggal
            for ($i = 1; $i <= 3; $i++) {
                $mobil = $mobils->random();
                $jadwalDate = Carbon::now()->addDays($i);
                $jamBerangkat = Carbon::parse('08:00:00');
                $jamTiba = $jamBerangkat->copy()->addMinutes($rute->estimasi_waktu);

                // Tetapkan agen secara acak untuk setiap jadwal
                $randomAgenId = $agenIds[array_rand($agenIds)];

                $jadwal = Jadwal::create([
                    'agen_id' => $randomAgenId, // Menggunakan ID agen acak
                    'rute_id' => $rute->id,
                    'mobil_id' => $mobil->id,
                    'kode_jadwal' => 'JDL-' . strtoupper(Str::random(10)), // Gunakan Str::random() untuk kode unik
                    'tanggal' => $jadwalDate->toDateString(),
                    'jam_berangkat' => $jamBerangkat->format('H:i:s'),
                    'jam_tiba_estimasi' => $jamTiba->format('H:i:s'),
                    'harga' => $rute->harga_dasar,
                    'kursi_tersedia' => $mobil->kapasitas,
                    'status' => 'Dijadwalkan',
                    'catatan' => null,
                ]);

                // attach 1-2 driver secara pivot
                $assignedDrivers = $drivers->random(rand(1, min(2, $drivers->count())));
                foreach ($assignedDrivers as $d) {
                    DB::table('jadwal_driver')->insert([
                        'jadwal_id' => $jadwal->id,
                        'driver_id' => $d->id,
                    ]);
                }

                // attach 1-2 crew secara pivot
                $assignedCrews = $crews->random(rand(1, min(2, $crews->count())));
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
