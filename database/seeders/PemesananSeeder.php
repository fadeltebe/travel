<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pemesanan;
use App\Models\Jadwal;
use Illuminate\Support\Str;
use Carbon\Carbon;

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

                Pemesanan::create([
                    'agen_id' => $jadwal->agen_id,
                    'user_id' => null, // tidak mengisi user untuk seed ini
                    'jadwal_id' => $jadwal->id,
                    'kode_pemesanan' => 'PMN-' . strtoupper(Str::random(8)),
                    'nama_pemesan' => 'Pemesan ' . substr(Str::random(6), 0, 6),
                    'telepon_pemesan' => '0812' . rand(10000000, 99999999),
                    'email_pemesan' => null,
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
