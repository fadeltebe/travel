<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PemesananPenumpangsSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil data id yang dibutuhkan
        $agenIds = DB::table('agens')->pluck('id')->toArray();
        $pemesananIds = DB::table('pemesanans')->pluck('id')->toArray();
        $penumpangIds = DB::table('penumpangs')->pluck('id')->toArray();

        // Pastikan data tersedia
        if (empty($agenIds) || empty($pemesananIds) || empty($penumpangIds)) {
            return;
        }

        // Contoh seed 3 data, round-robin id
        $data = [];
        for ($i = 0; $i < 3; $i++) {
            $data[] = [
                'agen_id' => $agenIds[$i % count($agenIds)],
                'pemesanan_id' => $pemesananIds[$i % count($pemesananIds)],
                'penumpang_id' => $penumpangIds[$i % count($penumpangIds)],
                'nomor_kursi' => $i + 1,
                'harga' => 250000 + ($i * 50000),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('pemesanan_penumpangs')->insert($data);
    }
}
