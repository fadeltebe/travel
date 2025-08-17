<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Jadwal;
use App\Models\Pemesanan;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class PemesananSeeder extends Seeder
{
    public function run(): void
    {
        $jadwals = Jadwal::all();

        foreach ($jadwals as $jadwal) {
            // buat 1-3 pemesanan per jadwal
            $count = rand(1, 3);
            for ($i = 0; $i < $count; $i++) {
                $jumlah = rand(1, 3);
                $total = $jadwal->harga * $jumlah;
                $statuses = ['pending', 'confirmed', 'paid', 'cancelled'];
                $status = $statuses[array_rand($statuses)];

                // Ambil satu pemesan_id secara acak dari tabel pemesans
                $pemesanId = DB::table('pemesans')->inRandomOrder()->value('id');

                Pemesanan::create([
                    'agen_id' => $jadwal->agen_id,
                    'pemesan_id' => $pemesanId,
                    'jadwal_id' => $jadwal->id,
                    'kode_pemesanan' => 'PMN-' . strtoupper(Str::random(8)),
                    'jumlah_penumpang' => $jumlah,
                    'total_harga' => $total,
                    'status' => $status,
                    'expired_at' => Carbon::now()->addDays(1),
                    'catatan' => null,
                ]);
            }
        }
    }
}
