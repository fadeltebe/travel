<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Jadwal;
use App\Models\Crew;
use Illuminate\Support\Facades\DB;

class JadwalCrewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $crews = Crew::all();

        foreach (Jadwal::all() as $jadwal) {
            // Pilih 1-2 crew secara acak untuk setiap jadwal
            $assignedCrews = $crews->random(rand(1, min(2, $crews->count())));

            foreach ($assignedCrews as $crew) {
                DB::table('jadwal_crew')->updateOrInsert(
                    [
                        'jadwal_id' => $jadwal->id,
                        'crew_id' => $crew->id,
                    ],
                    []
                );
            }
        }
    }
}
