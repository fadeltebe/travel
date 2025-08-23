<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PemesananPenumpangsSeeder extends Seeder
{
    public function run(): void
    {
        $pemesananList = DB::table('pemesanans')->get();
        $penumpangIds = DB::table('penumpangs')->pluck('id')->toArray();
        $agenIds = DB::table('agens')->pluck('id')->toArray();

        if ($pemesananList->isEmpty() || empty($penumpangIds) || empty($agenIds)) {
            return;
        }

        $data = [];
        $penumpangCount = count($penumpangIds);
        $penumpangIndex = 0;

        foreach ($pemesananList as $i => $pemesanan) {
            $jadwal_id = $pemesanan->jadwal_id;
            $agen_id = $pemesanan->agen_id ?? $agenIds[$i % count($agenIds)];
            $pemesanan_id = $pemesanan->id;

            // Set jumlah penumpang per pemesanan (misal 2-3 penumpang)
            $jumlahPenumpang = rand(2, 3);

            for ($j = 0; $j < $jumlahPenumpang; $j++) {
                // Ambil penumpang secara berurutan/round-robin
                $penumpang_id = $penumpangIds[$penumpangIndex % $penumpangCount];
                $penumpangIndex++;

                $data[] = [
                    'agen_id' => $agen_id,
                    'jadwal_id' => $jadwal_id,
                    'pemesanan_id' => $pemesanan_id,
                    'penumpang_id' => $penumpang_id,
                    'nomor_kursi' => $j + 1,
                    'harga' => 250000 + ($i * 50000),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        DB::table('pemesanan_penumpangs')->insert($data);
    }
}
